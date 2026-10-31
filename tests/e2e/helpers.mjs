// Apoio dos testes de navegador: abre o Chrome instalado (puppeteer-core) e audita páginas.
import fs from 'node:fs';
import puppeteer from 'puppeteer-core';

export const BASE = (process.env.KADE_TEST_URL || 'http://kade.test').replace(/\/$/, '');
export const ADMIN = [process.env.KADE_TEST_ADMIN_LOGIN || 'admin', process.env.KADE_TEST_ADMIN_PASSWORD || 'Teste@123'];
export const USER = [process.env.KADE_TEST_USER_LOGIN || 'clienteteste', process.env.KADE_TEST_USER_PASSWORD || 'Teste@123'];

const CHROME_CANDIDATES = [
	process.env.CHROME_PATH,
	'C:/Program Files/Google/Chrome/Application/chrome.exe',
	'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
	'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
	'/usr/bin/google-chrome',
	'/usr/bin/chromium',
	'/usr/bin/chromium-browser',
].filter(Boolean);

export function chromePath() {
	return CHROME_CANDIDATES.find((p) => fs.existsSync(p));
}

export async function launch() {
	const executablePath = chromePath();
	if (!executablePath)
		throw new Error('Chrome não encontrado: defina CHROME_PATH');
	return puppeteer.launch({ executablePath, headless: true, args: ['--no-first-run', '--disable-gpu'] });
}

export async function siteIsUp() {
	try {
		return (await fetch(BASE + '/terms_use/')).status === 200;
	} catch {
		return false;
	}
}

/**
 * Nova aba que anota erros de JavaScript e recursos do próprio site que falharam.
 * `page.problems` acumula as mensagens; os testes conferem que fica vazio.
 */
export async function openPage(context, { width = 1280, height = 900 } = {}) {
	const page = await context.newPage();
	await page.setViewport({ width, height });
	page.problems = [];
	page.on('pageerror', (err) => page.problems.push('JS: ' + err.message));
	page.on('console', (msg) => {
		if (msg.type() === 'error' && !/Failed to load resource/.test(msg.text()))
			page.problems.push('console: ' + msg.text());
	});
	page.on('response', (res) => {
		const req = res.request();
		// O documento principal pode ser 404 de propósito; recursos do site não
		if (res.url().startsWith(BASE) && res.status() >= 400 && req.resourceType() !== 'document')
			page.problems.push(`HTTP ${res.status()}: ${res.url()}`);
	});
	page.on('requestfailed', (req) => {
		if (req.url().startsWith(BASE) && !/ERR_ABORTED/.test(req.failure()?.errorText || ''))
			page.problems.push(`falhou: ${req.url()} (${req.failure()?.errorText})`);
	});
	return page;
}

export async function login(page, [user, password]) {
	await page.goto(BASE + '/login/', { waitUntil: 'networkidle2' });
	await page.type('#user_login', user);
	await page.type('#user_password', password);
	await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('#form_login button[type=submit]')]);
	if (page.url().includes('/login/'))
		throw new Error(`Login de "${user}" falhou: confira KADE_TEST_*_LOGIN/PASSWORD`);
}

/**
 * Regras básicas de qualidade da página aberta: um h1, imagens com alt, campos com
 * rótulo e nada mais largo que a tela.
 */
export async function audit(page) {
	return page.evaluate(() => {
		const visible = (el) => {
			const s = getComputedStyle(el);
			const r = el.getBoundingClientRect();
			return s.display !== 'none' && s.visibility !== 'hidden' && (r.width > 0 || r.height > 0);
		};
		const labelled = (el) =>
			el.closest('label') ||
			(el.id && document.querySelector(`label[for="${CSS.escape(el.id)}"]`)) ||
			el.getAttribute('aria-label') || el.getAttribute('aria-labelledby') || el.getAttribute('title');
		const controls = [...document.querySelectorAll('input, select, textarea')]
			.filter((el) => !['hidden', 'submit', 'button'].includes(el.type) && visible(el));
		// Elementos mais largos que a tela (causa de rolagem lateral no celular)
		const wide = [...document.querySelectorAll('body *')]
			.filter((el) => visible(el) && el.getBoundingClientRect().right > document.documentElement.clientWidth + 1)
			.filter((el) => !el.closest('.k\\:overflow-x-auto, [class*="overflow-x-auto"], .k-banner-track, nav'))
			.slice(0, 3)
			.map((el) => el.tagName.toLowerCase() + (el.id ? '#' + el.id : '') + '.' + [...el.classList].slice(0, 3).join('.'));
		return {
			h1: document.querySelectorAll('h1').length,
			imgsWithoutAlt: [...document.querySelectorAll('img')].filter((i) => !i.hasAttribute('alt')).map((i) => i.src),
			unlabeled: controls.filter((el) => !labelled(el)).map((el) => el.name || el.id || el.outerHTML.slice(0, 60)),
			scrollsSideways: document.documentElement.scrollWidth > document.documentElement.clientWidth + 1,
			wide,
			title: document.title,
		};
	});
}
