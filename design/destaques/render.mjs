// Gera os banners de "Destaques" a partir de banners.html:
//   node design/destaques/render.mjs
// Cada <section> (#b1, #b2, #b3) vira kade/shared/images/banner_slider1..3.jpg, em 2200 x 600.
// Usa o mesmo Chrome dos testes de navegador (CHROME_PATH para outro caminho).
import { launch } from '../../tests/e2e/helpers.mjs';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { dirname, join } from 'node:path';

const here = dirname(fileURLToPath(import.meta.url));
const images = join(here, '..', '..', 'kade', 'shared', 'images');

const browser = await launch();
const page = await browser.newPage();
await page.setViewport({ width: 1200, height: 1000, deviceScaleFactor: 2 });
await page.goto(pathToFileURL(join(here, 'banners.html')).href, { waitUntil: 'networkidle0' });
await page.evaluate(() => document.fonts.ready);
for (const [i, id] of ['b1', 'b2', 'b3'].entries()) {
	const path = join(images, `banner_slider${i + 1}.jpg`);
	await (await page.$('#' + id)).screenshot({ path, type: 'jpeg', quality: 88 });
	console.log(path);
}
await browser.close();
