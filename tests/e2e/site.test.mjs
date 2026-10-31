// Testes de navegador (Chrome de verdade) contra o site local.
// Rodar com: npm run test:e2e   (precisa do site em KADE_TEST_URL e do Chrome instalado)
import { after, before, describe, test } from 'node:test';
import assert from 'node:assert/strict';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { ADMIN, BASE, USER, accessibility, audit, launch, login, openPage, siteIsUp } from './helpers.mjs';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const up = await siteIsUp();
const skip = up ? false : `site fora do ar em ${BASE}`;

const PUBLIC_PAGES = ['/', '/quem_somos/', '/terms_use/', '/login/', '/request_new_password/', '/cad_assinante/', '/cad_veiculo/', '/pagina_inexistente/'];
const USER_PAGES = ['/busca_veiculo/', '/data_user/', '/view_parc/'];
const ADMIN_PAGES = ['/view_custumers/', '/gnrt_parc/', '/alt_parc/action=edit&windowPopUp=true', '/load_banner/', '/view_log_sms/', '/mc_client/?windowPopUp=1'];

let browser;
const sessions = {};

before(async () => {
	if (skip) return;
	browser = await launch();
	// Uma janela anônima por perfil: os cookies não se misturam
	for (const [name, creds] of [['anon', null], ['user', USER], ['admin', ADMIN]]) {
		sessions[name] = await browser.createBrowserContext();
		if (creds) {
			const page = await openPage(sessions[name]);
			await login(page, creds);
			await page.close();
		}
	}
});

after(async () => {
	await browser?.close();
});

async function checkPage(context, url, viewport) {
	const page = await openPage(context, viewport);
	await page.goto(BASE + url, { waitUntil: 'networkidle2' });
	const result = await audit(page);
	const a11y = await accessibility(page);
	const problems = page.problems;
	await page.close();

	assert.deepEqual(problems, [], `${url}: erros no navegador`);
	assert.deepEqual(a11y, [], `${url}: problemas de acessibilidade (axe-core)`);
	assert.equal(result.h1, 1, `${url}: deve ter exatamente um <h1> (tem ${result.h1})`);
	assert.deepEqual(result.imgsWithoutAlt, [], `${url}: imagens sem alt`);
	assert.deepEqual(result.unlabeled, [], `${url}: campos sem rótulo`);
	assert.ok(result.title.includes('KADE'), `${url}: título da aba`);
	if (viewport.width < 500)
		assert.equal(result.scrollsSideways, false, `${url}: rola para o lado no celular (${result.wide.join(', ')})`);
}

for (const [profile, pages] of [['anon', PUBLIC_PAGES], ['user', USER_PAGES], ['admin', ADMIN_PAGES]]) {
	describe(`páginas (${profile})`, { skip }, () => {
		for (const url of pages) {
			test(`${url} no computador`, () => checkPage(sessions[profile], url, { width: 1280, height: 900 }));
			test(`${url} no celular`, () => checkPage(sessions[profile], url, { width: 390, height: 844 }));
		}
	});
}

describe('fluxos', { skip }, () => {
	test('mapa da home: clicar em um estado carrega as cidades', async () => {
		const page = await openPage(sessions.anon);
		await page.goto(BASE + '/', { waitUntil: 'networkidle2' });
		assert.equal(await page.$$eval('g.k-uf', (els) => els.length), 27, '27 unidades da federação no mapa');

		const before = await page.$eval('#k_maps_table', (el) => el.innerText);
		await page.click('g.k-uf[data-uf="PR"]');
		await page.waitForFunction((old) => document.querySelector('#k_maps_table').innerText !== old, { timeout: 10000 }, before);
		assert.ok(await page.$eval('g.k-uf[data-uf="PR"]', (el) => el.classList.contains('is-selected')), 'PR marcado no mapa');
		assert.deepEqual(page.problems, []);
		await page.close();
	});

	test('login com senha errada mostra o aviso e não entra', async () => {
		const context = await browser.createBrowserContext();
		const page = await openPage(context);
		// Login inexistente: senhas erradas na conta de teste contariam para o bloqueio
		await assert.rejects(login(page, ['nao_existe_' + Date.now(), 'senha-errada']), /Login .* falhou/);
		assert.ok((await page.content()).includes('Usuário ou senha inválidos'));
		await context.close();
	});

	test('nova senha: a resposta não diz se os dados conferem', async () => {
		const context = await browser.createBrowserContext();
		const page = await openPage(context);
		await page.goto(BASE + '/request_new_password/', { waitUntil: 'networkidle2' });
		await page.type('#newpwd_cpf', '93541134780');
		await page.type('#newpwd_mail', 'ninguem@example.com');
		await page.click('#button_sender');
		await page.waitForFunction(() => document.body.innerText.includes('Se os dados conferirem'), { timeout: 10000 });
		assert.deepEqual(page.problems, []);
		await context.close();
	});

	test('imagens da home: escolher um arquivo mostra a prévia antes de enviar', async () => {
		const page = await openPage(sessions.admin);
		await page.goto(BASE + '/load_banner/', { waitUntil: 'networkidle2' });
		const input = await page.$('#banner_file');
		await input.uploadFile(path.join(ROOT, 'kade/shared/images/banner_ori/banner_slider1.jpg'));
		await page.waitForFunction(() => document.querySelector('#banner_img').src.startsWith('blob:'));
		assert.match(await page.$eval('#banner_state', (el) => el.innerText), /Prévia/);

		// Trocar o espaço descarta a prévia e volta para a imagem salva
		await page.select('#banner_id', '2');
		assert.doesNotMatch(await page.$eval('#banner_img', (el) => el.src), /^blob:/);
		assert.deepEqual(page.problems, []);
		await page.close();
	});

	test('nova conta: gerar parcelas preenche a tabela', async () => {
		const page = await openPage(sessions.admin);
		await page.goto(BASE + '/alt_parc/action=edit&windowPopUp=true', { waitUntil: 'networkidle2' });
		await page.$eval('#account_validate_month', (el) => { el.value = '6'; });
		await page.click('#button_ger_parc');
		await page.waitForFunction(() => document.querySelectorAll('#tbody_installment input[name="installment_value[]"]').length === 6, { timeout: 10000 });
		const values = await page.$$eval('#tbody_installment input[name="installment_value[]"]', (els) => els.map((e) => e.value));
		assert.ok(values.every((v) => Number(v) === 70), `valores das parcelas: ${values}`);
		assert.deepEqual(page.problems, []);
		await page.close();
	});

	test('tabela ordena pelo teclado (Tab + Enter no cabeçalho)', async () => {
		const page = await openPage(sessions.admin);
		await page.goto(BASE + '/view_custumers/', { waitUntil: 'networkidle2' });
		await page.focus('th[data-col="per.name"]');
		await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.keyboard.press('Enter')]);
		assert.equal(await page.$eval('#order_column', (el) => el.value), 'per.name');
		assert.deepEqual(page.problems, []);
		await page.close();
	});

	test('cadastro de veículo vazio não é enviado', async () => {
		const context = await browser.createBrowserContext();
		const page = await openPage(context);
		await page.goto(BASE + '/cad_veiculo/', { waitUntil: 'networkidle2' });
		await page.click('#button_save');
		// Continua na mesma página e não registra nada
		await new Promise((r) => setTimeout(r, 1500));
		assert.ok(page.url().includes('/cad_veiculo/'));
		assert.deepEqual(page.problems, []);
		await context.close();
	});
});
