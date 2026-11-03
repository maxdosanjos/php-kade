// Grava um vídeo de demonstração (MP4) do site local (KADE_TEST_URL, padrão http://kade.test):
//   FFMPEG_PATH=caminho/do/ffmpeg node demo/gravar_video.mjs [saida.mp4]
//
// Use com os dados de demonstração carregados (demo/kade_demo.sql): o roteiro entra como
// "rotasul" e "admin" e CRIA registros (um veículo, um assinante, uma mensagem de WhatsApp).
// O captcha é lido da sessão do PHP (C:\laragon\tmp, ou SESSION_DIR) e o e-mail de
// confirmação, do Mailpit (http://localhost:8025). Precisa do ffmpeg (FFMPEG_PATH ou no PATH).
import { launch, BASE } from '../tests/e2e/helpers.mjs';
import { readFileSync, existsSync, writeFileSync, mkdtempSync, rmSync } from 'node:fs';
import { spawnSync } from 'node:child_process';
import { tmpdir } from 'node:os';
import { createHmac } from 'node:crypto';
import { join } from 'node:path';

const OUT = process.argv[2] || 'demo/kade_demo.mp4';
const SESSION_DIR = process.env.SESSION_DIR || 'C:/laragon/tmp';
const MAILPIT = process.env.KADE_TEST_MAILPIT || 'http://localhost:8025';
const env = Object.fromEntries(readFileSync('.env', 'utf8').split(/\r?\n/).map((l) => l.match(/^([A-Z0-9_]+)=(.*)$/)).filter(Boolean).map((m) => [m[1], m[2].trim()]));

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const stamp = Date.now().toString().slice(-4);

/** CPF com dígitos verificadores válidos */
function cpf() {
	const d = Array.from({ length: 9 }, () => Math.floor(Math.random() * 10));
	for (const t of [9, 10]) {
		const s = d.reduce((acc, n, i) => acc + n * (t + 1 - i), 0);
		d.push(((10 * s) % 11) % 10);
	}
	return d.join('');
}

// Legenda e cursor desenhados na página (o navegador automático não mostra o mouse)
const overlay = () => {
	// Só na página principal: os formulários recebem a resposta (JSON) num iframe
	if (window.top !== window || document.contentType !== 'text/html')
		return;
	const add = () => {
		if (document.getElementById('demo-caption')) return;
		const style = document.createElement('style');
		style.textContent = `
			#demo-caption { position: fixed; left: 50%; bottom: 28px; transform: translateX(-50%); z-index: 2147483647; max-width: 86%;
				background: rgba(11, 37, 69, .94); color: #fff; font: 600 20px/1.35 Inter, system-ui, sans-serif; padding: 14px 26px;
				border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,.3); text-align: center; transition: opacity .3s; pointer-events: none; }
			#demo-caption small { display: block; font-weight: 400; font-size: 15px; color: #d5deeb; margin-top: 4px; }
			#demo-caption:empty { opacity: 0; }
			#demo-cursor { position: fixed; left: 0; top: 0; z-index: 2147483647; width: 26px; height: 26px; pointer-events: none;
				transition: transform .7s cubic-bezier(.4,0,.2,1); transform: translate(640px, 360px); }
			#demo-cursor.click::after { content: ""; position: absolute; left: -9px; top: -9px; width: 26px; height: 26px; border-radius: 50%;
				background: rgba(255, 138, 0, .45); animation: demo-click .45s ease-out; }
			@keyframes demo-click { from { transform: scale(.3); opacity: 1; } to { transform: scale(1.6); opacity: 0; } }
			#demo-chat { position: fixed; right: 28px; top: 90px; z-index: 2147483646; width: 360px; background: #e9eef4; border-radius: 18px;
				box-shadow: 0 16px 40px rgba(0,0,0,.28); font: 15px/1.4 Inter, system-ui, sans-serif; overflow: hidden; }
			#demo-chat header { background: #0b3b6f; color: #fff; padding: 12px 16px; font-weight: 600; }
			#demo-chat .msgs { padding: 14px; display: flex; flex-direction: column; gap: 10px; }
			#demo-chat .out, #demo-chat .in { padding: 10px 12px; border-radius: 12px; white-space: pre-wrap; max-width: 85%; }
			#demo-chat .out { align-self: flex-end; background: #ffd9a8; font-family: ui-monospace, monospace; font-weight: 600; }
			#demo-chat .in { align-self: flex-start; background: #fff; }`;
		document.head.appendChild(style);
		const caption = document.createElement('div');
		caption.id = 'demo-caption';
		const cursor = document.createElement('div');
		cursor.id = 'demo-cursor';
		cursor.innerHTML = '<svg width="26" height="26" viewBox="0 0 24 24"><path d="M4 2l16 10-7 1.5L9.5 21z" fill="#0f1b2d" stroke="#fff" stroke-width="1.6" stroke-linejoin="round"/></svg>';
		const pos = sessionStorage.getItem('demo-cursor');
		if (pos) { cursor.style.transition = 'none'; cursor.style.transform = pos; requestAnimationFrame(() => (cursor.style.transition = '')); }
		document.body.append(caption, cursor);
	};
	document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', add) : add();
};

const browser = await launch();
const page = await browser.newPage();
await page.setViewport({ width: 1280, height: 720 });
await page.evaluateOnNewDocument(overlay);

const caption = async (title, detail = '') => {
	await page.waitForSelector('#demo-caption');
	await page.evaluate((t, d) => { document.getElementById('demo-caption').innerHTML = t + (d ? `<small>${d}</small>` : ''); }, title, detail);
};
const go = async (path, title, detail) => {
	await page.goto(path.startsWith('http') ? path : BASE + path, { waitUntil: 'networkidle2' });
	if (title) await caption(title, detail);
	await sleep(1800);
};
const scrollTo = async (target, wait = 1800) => {
	await page.evaluate((t) => {
		const y = typeof t === 'number' ? t : document.querySelector(t).getBoundingClientRect().top + scrollY - 90;
		scrollTo({ top: y, behavior: 'smooth' });
	}, target);
	await sleep(wait);
};
/** Move o cursor até o elemento e clica */
const click = async (selector, wait = 900) => {
	const el = await page.waitForSelector(selector, { visible: true });
	await el.evaluate((e) => e.scrollIntoView({ block: 'center', behavior: 'smooth' }));
	await sleep(500);
	const box = await el.boundingBox();
	const x = box.x + Math.min(box.width / 2, 60), y = box.y + box.height / 2;
	await page.evaluate((x, y) => {
		const c = document.getElementById('demo-cursor');
		c.style.transform = `translate(${x}px, ${y}px)`;
		sessionStorage.setItem('demo-cursor', c.style.transform);
	}, x, y);
	await sleep(750);
	await page.evaluate(() => { const c = document.getElementById('demo-cursor'); c.classList.remove('click'); void c.offsetWidth; c.classList.add('click'); });
	await page.mouse.click(x, y);
	await sleep(wait);
};
const type = async (selector, text) => {
	await click(selector, 200);
	await page.type(selector, text, { delay: 55 });
	await sleep(250);
};
const select = async (selector, value) => {
	await click(selector, 200);
	await page.select(selector, value);
	await sleep(600);
};
/** Código do captcha guardado na sessão do PHP, como o visitante leria na imagem */
const captcha = async (key) => {
	const sid = (await page.cookies()).find((c) => c.name === 'PHPSESSID')?.value;
	const file = join(SESSION_DIR, 'sess_' + sid);
	const match = existsSync(file) && readFileSync(file, 'utf8').match(new RegExp(key + '\\|s:\\d+:"([^"]+)"'));
	if (!match) throw new Error('Captcha não encontrado na sessão ' + file);
	return match[1];
};
const textAppears = (text, timeout = 20000) => page.waitForFunction((t) => document.body.innerText.includes(t), { timeout }, text);
/** Fecha a caixa de mensagem aberta (jQuery UI), se houver */
const closeDialog = async () => {
	await page.waitForNetworkIdle({ idleTime: 600, timeout: 10000 }).catch(() => {});
	const found = await page.evaluate(() => {
		const dialog = [...document.querySelectorAll('.ui-dialog')].find((d) => d.offsetParent !== null);
		const button = dialog && (dialog.querySelector('.ui-dialog-buttonpane button') || dialog.querySelector('.ui-dialog-titlebar-close'));
		if (button) button.setAttribute('data-demo-close', '');
		return !!button;
	}).catch(() => false); // a página pode ter recarregado (ex.: depois de "Marcar como utilizado")
	if (found) {
		await click('[data-demo-close]');
		await page.evaluate(() => document.querySelectorAll('[data-demo-close]').forEach((b) => b.removeAttribute('data-demo-close')));
	}
};
const login = async (user, password) => {
	await go('/login/');
	await type('#user_login', user);
	await type('#user_password', password);
	await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), click('#form_login button[type=submit]', 0)]);
	await sleep(1200);
};
const logout = async () => {
	await page.deleteCookie(...(await page.cookies()).filter((c) => c.name === 'PHPSESSID'));
};

// Os quadros do Chrome (screencast do DevTools) vão para JPGs numa pasta temporária e o
// ffmpeg monta o MP4 (H.264) no fim, com todos os núcleos. O gravador do puppeteer codifica
// ao vivo com um núcleo só e deixa o navegador lento.
const FFMPEG = process.env.FFMPEG_PATH || 'ffmpeg';
const FRAMES = mkdtempSync(join(tmpdir(), 'kade-video-'));
const frames = [];
const cdp = await page.createCDPSession();
cdp.on('Page.screencastFrame', ({ data, metadata, sessionId }) => {
	const file = join(FRAMES, `f${String(frames.length).padStart(6, '0')}.jpg`);
	writeFileSync(file, Buffer.from(data, 'base64'));
	frames.push({ file, t: metadata.timestamp });
	cdp.send('Page.screencastFrameAck', { sessionId }).catch(() => {});
});
await cdp.send('Page.startScreencast', { format: 'jpeg', quality: 90, maxWidth: 1280, maxHeight: 720 });
const recorder = { stop: () => cdp.send('Page.stopScreencast').catch(() => {}) };
try {
	// 1. Home
	await go('/', 'KADE Caminhões: caminhões vazios em todo o Brasil, em tempo real', 'Página inicial, sem login');
	await sleep(1500);
	await scrollTo('#k_maps_table', 1500);
	await caption('Mapa: quantos veículos estão disponíveis em cada estado');
	await click('g.k-uf[data-uf="MT"]', 2200);
	await caption('Ao clicar num estado, aparecem as cidades com mais caminhões', 'Mato Grosso');
	await sleep(1200);
	await click('g.k-uf[data-uf="PR"]', 2600);
	await scrollTo('section[aria-label="Destaques"]', 1200);
	await caption('Destaques: banners gerenciados pelo administrador');
	await sleep(1500);
	await click('.k-banner-next', 1800);
	await click('.k-banner-next', 2200);

	// 2. Quem somos
	await go('/quem_somos/', 'Quem somos: como a KADE funciona');
	await scrollTo('#como_funciona', 2800);
	await caption('O motorista anuncia pelo site ou por mensagem: SMS, WhatsApp ou Telegram', 'Código do tipo de veículo + CEP, ex.: 6*83203010');
	await sleep(2500);

	// 3. Cadastro de veículo pelo site
	await go('/cad_veiculo/', 'Motorista cadastra o caminhão pelo site', 'Sem login: CPF, nome, tipo, telefone e CEP');
	await type('#vehicle_cpf', cpf());
	await type('#vehicle_name', 'Rogério Antunes Demo');
	await select('#vehicle_type', '6');
	await type('#vehicle_phone', '43900001234');
	await caption('A cidade e a UF vêm do CEP, automaticamente');
	await type('#vehicle_zipcode', '86010000');
	await page.keyboard.press('Tab');
	await page.waitForFunction(() => document.querySelector('#vehicle_city').value !== '', { timeout: 15000 });
	await sleep(1800);
	await caption('Código de segurança contra robôs');
	await type('#vehicle_captcha', await captcha('vehicle_captcha'));
	await click('#button_save', 500);
	await textAppears('realizado com sucesso');
	await caption('Pronto: o veículo já aparece no mapa para as empresas');
	await sleep(2800);
	await closeDialog();

	// 4. Mensagem pelo WhatsApp (chamada assinada como a Meta faz)
	if (env.WHATSAPP_APP_SECRET) {
		await go('/', 'Outro motorista anuncia pelo WhatsApp', 'A mensagem chega ao site e a resposta volta na conversa');
		const body = JSON.stringify({ object: 'whatsapp_business_account', entry: [{ id: '1', changes: [{ field: 'messages', value: {
			messaging_product: 'whatsapp', metadata: { phone_number_id: env.WHATSAPP_PHONE_NUMBER_ID || '123' },
			messages: [{ from: '5566900004321', id: 'wamid.VIDEO' + Date.now(), timestamp: '1', type: 'text', text: { body: '3*78890000' } }] } }] }] });
		const sig = 'sha256=' + createHmac('sha256', env.WHATSAPP_APP_SECRET).update(body).digest('hex');
		const reply = (await (await fetch(BASE + '/webhook/whatsapp', { method: 'POST', body, headers: { 'Content-Type': 'application/json', 'X-Hub-Signature-256': sig } })).json()).replies?.[0]?.text || '';
		await page.evaluate((reply) => {
			const chat = document.createElement('div');
			chat.id = 'demo-chat';
			chat.innerHTML = '<header>Conversa com a KADE (WhatsApp)</header><div class="msgs"><div class="out">3*78890000</div></div>';
			document.body.appendChild(chat);
			setTimeout(() => { const m = document.createElement('div'); m.className = 'in'; m.textContent = reply; chat.querySelector('.msgs').appendChild(m); }, 1600);
		}, reply);
		await sleep(6500);
	}

	// 5. Assinante (transportadora) se cadastra
	await go('/cad_assinante/', 'Transportadoras e embarcadores fazem a assinatura', 'Cadastro de pessoa física ou jurídica');
	const mail = `transnorte${stamp}@example.com`;
	await type('#custumer_cpf', cpf());
	await type('#custumer_name', 'Helena Duarte Demo');
	await type('#custumer_phone', '41900005678');
	await type('#custumer_mail', mail);
	await type('#custumer_login', 'helena' + stamp);
	await type('#custumer_password', 'Demo@2026');
	await type('#custumer_zipcode', '80010000');
	await page.keyboard.press('Tab');
	await page.waitForFunction(() => document.querySelector('#custumer_city').value !== '', { timeout: 15000 });
	await sleep(800);
	await type('#custumer_address_number', '450');
	await type('#custumer_captcha', await captcha('custumer_captcha'));
	await click('#button_save', 500);
	await textAppears('e-mail', 20000);
	await caption('Um e-mail de confirmação é enviado ao assinante');
	await sleep(2500);
	const list = await (await fetch(`${MAILPIT}/api/v1/search?query=${encodeURIComponent('to:' + mail)}`)).json();
	const id = list.messages?.[0]?.ID;
	if (id) {
		await go(`${MAILPIT}/view/${id}`, 'E-mail de confirmação (Mailpit, a caixa de e-mails local)');
		await sleep(2500);
		const text = (await (await fetch(`${MAILPIT}/api/v1/message/${id}`)).json()).Text;
		const link = text.match(/https?:\/\/\S+\/confirm_user\/vua=[a-f0-9]+/)?.[0];
		if (link) await go(link, 'Ao clicar no link, o cadastro é confirmado');
		await sleep(2000);
	}

	// 6. Assinante logado: busca e uso do contato
	await login('rotasul', 'Demo@123');
	await go('/busca_veiculo/', 'Assinante logado: busca de veículos', 'Expresso Rota Sul (assinante de demonstração)');
	await select('#vehicle_region', 'MT');
	await select('#vehicle_type', '6');
	await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), click('#button_search', 0)]);
	await caption('Carretas LS graneleiras disponíveis em Mato Grosso');
	await scrollTo('#div_result', 2200);
	if (await page.$('tr.tr_hover td')) {
		await click('tr.tr_hover td', 600);
		await textAppears('Marcar como utilizado', 15000);
		await caption('O assinante vê o contato do motorista e liga direto', 'Depois, marca o registro como utilizado');
		await sleep(3200);
		await click('#button_check_view', 2500);
		await closeDialog();
	}
	await go('/reg_utilized/', 'Registros utilizados: os fretes que o assinante fechou');
	await sleep(1500);
	await go('/view_parc/', 'Minhas parcelas: a assinatura do cliente', 'Plano anual: as próximas mensalidades em aberto');
	await sleep(2500);
	await logout();

	// 7. Administrador
	await login('admin', 'Teste@123');
	await go('/view_custumers/', 'Administrador: clientes cadastrados', 'Busca por tipo, situação, UF e cidade');
	await click('#person_pj_label', 400);
	await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), click('#button_search', 0)]);
	await sleep(2200);
	await go('/gnrt_parc/', 'Contas e parcelas de cada assinante');
	await scrollTo(400, 2200);
	await go('/view_log_sms/', 'Log de mensagens: SMS, WhatsApp e Telegram', 'Cada mensagem recebida, com o resultado');
	await select('#channel', 'WHATSAPP');
	await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), click('#button_search', 0)]);
	await caption('A mensagem do WhatsApp de agora há pouco, já registrada');
	await scrollTo('#div_result', 3000);
	await go('/load_banner/', 'Imagens da home: o administrador troca a foto do topo e os banners');
	await scrollTo(450, 2200);

	// 8. Encerramento
	await go('/', 'KADE Caminhões', 'Cargas e caminhões conectados, em todo o Brasil');
	await sleep(3500);
} catch (e) {
	await page.screenshot({ path: OUT + '.erro.png' }).catch(() => {});
	console.error('Parou em ' + page.url() + ' (tela em ' + OUT + '.erro.png): ' + e.stack.split('\n').filter((l) => !l.includes('node_modules')).join('\n'));
	process.exitCode = 1;
} finally {
	await recorder.stop();
	await browser.close();
}
// Cada quadro fica na tela até o seguinte chegar (o Chrome só manda quadros quando algo
// muda), no máximo 3 segundos: as esperas do roteiro (rede, e-mail) não viram tela parada
const list = frames.map((f, i) => `file '${f.file.replace(/\\/g, '/')}'\nduration ${Math.min((frames[i + 1]?.t ?? f.t + 2) - f.t, 3).toFixed(3)}`);
writeFileSync(join(FRAMES, 'list.txt'), list.join('\n') + `\nfile '${frames.at(-1).file.replace(/\\/g, '/')}'\n`);
const r = spawnSync(FFMPEG, ['-y', '-loglevel', 'error', '-f', 'concat', '-safe', '0', '-i', join(FRAMES, 'list.txt'),
	'-vf', 'fps=30,scale=1280:720:force_original_aspect_ratio=decrease,pad=1280:720:(ow-iw)/2:(oh-ih)/2:color=white',
	'-c:v', 'libx264', '-preset', 'slow', '-crf', '20', '-pix_fmt', 'yuv420p', '-movflags', '+faststart', OUT], { stdio: 'inherit' });
rmSync(FRAMES, { recursive: true, force: true });
if (r.status !== 0)
	throw new Error('Falha ao gerar o MP4');
console.log(`Vídeo salvo em ${OUT} (${frames.length} quadros)`);
