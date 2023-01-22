const { readFile, writeFile } = require('fs-extra');
const { join } = require('path');

const RootPath = process.cwd();

/**
 * Main method that will patch files...
 *
 * @param options The options from setting.json
 *
 * @returns {Promise}
 */
module.exports.patchPackages = async (options) => {
  const mediaVendorPath = join(RootPath, 'media/vendor');

  // Joomla's hack to expose the chosen base classes so we can extend it ourselves
  // (it was better than the many hacks we had before. But I'm still ashamed of myself).
  let dest = join(mediaVendorPath, 'chosen');
  const chosenPath = `${dest}/${options.settings.vendors['chosen-js'].js['chosen.jquery.js']}`;
  let ChosenJs = await readFile(chosenPath, { encoding: 'utf8' });
  ChosenJs = ChosenJs.replace('}).call(this);', `  document.AbstractChosen = AbstractChosen;
  document.Chosen = Chosen;
}).call(this);`);
  await writeFile(chosenPath, ChosenJs, { encoding: 'utf8', mode: 0o644 });

  // Append initialising code to the end of the Short-and-Sweet javascript
  dest = join(mediaVendorPath, 'short-and-sweet');
  const shortandsweetPath = `${dest}/${options.settings.vendors['short-and-sweet'].js['dist/short-and-sweet.min.js']}`;
  let ShortandsweetJs = await readFile(shortandsweetPath, { encoding: 'utf8' });
  ShortandsweetJs = ShortandsweetJs.concat(`
shortAndSweet('textarea.charcount,input.charcount', {counterClassName: 'small text-muted'});
/** Repeatable */
document.addEventListener("joomla:updated", (event) => [].slice.call(event.target.querySelectorAll('textarea.charcount,input.charcount')).map((el) => shortAndSweet(el, {counterClassName: 'small text-muted'})));
`);
  await writeFile(shortandsweetPath, ShortandsweetJs, { encoding: 'utf8', mode: 0o644 });

  // Patch the Font Awesome math.div sass deprecations
  // _larger.scss
  let faPath = join(mediaVendorPath, 'fontawesome-free/scss/_larger.scss');
  let newScss = (await readFile(faPath, { encoding: 'utf8' })).replace('(4em / 3)', '(4em * .333)').replace('(3em / 4)', '(3em * .25)');
  await writeFile(faPath, newScss, { encoding: 'utf8', mode: 0o644 });
  await writeFile(join(RootPath, 'node_modules/@fortawesome/fontawesome-free/scss/_larger.scss'), newScss, { encoding: 'utf8', mode: 0o644 });
  // _list.scss
  faPath = join(mediaVendorPath, 'fontawesome-free/scss/_list.scss');
  newScss = (await readFile(faPath, { encoding: 'utf8' })).replace('5/4', '1.25');
  await writeFile(faPath, newScss, { encoding: 'utf8', mode: 0o644 });
  await writeFile(join(RootPath, 'node_modules/@fortawesome/fontawesome-free/scss/_list.scss'), newScss, { encoding: 'utf8', mode: 0o644 });
  // _variables.scss
  faPath = join(mediaVendorPath, 'fontawesome-free/scss/_variables.scss');
  newScss = (await readFile(faPath, { encoding: 'utf8' })).replace('(20em / 16)', '(20em * .0625)');
  await writeFile(faPath, newScss, { encoding: 'utf8', mode: 0o644 });
  await writeFile(join(RootPath, 'node_modules/@fortawesome/fontawesome-free/scss/_variables.scss'), newScss, { encoding: 'utf8', mode: 0o644 });
};
