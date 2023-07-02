const {
  readdir, readFile, writeFile, unlink,
} = require('fs').promises;
const { resolve } = require('path');
const { minify } = require('terser');
const rimraf = require('rimraf');
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const replace = require('@rollup/plugin-replace');
const { babel } = require('@rollup/plugin-babel');
const bsVersion = require('../../../package.json').dependencies.bootstrap.replace(/^\^|~/, '');

const tasks = [];
const inputFolder = 'build/media_source/vendor/bootstrap/js';
const outputFolder = 'media/vendor/bootstrap/js';

const createMinified = async (file) => {
  const initial = await readFile(resolve(outputFolder, file), { encoding: 'utf8' });
  const mini = await minify(initial.replace('./popper.js', `./popper.min.js?${bsVersion}`).replace('./dom.js', `./dom.min.js?${bsVersion}`), { sourceMap: false, format: { comments: false } });
  await writeFile(resolve(outputFolder, file), initial.replace('./popper.js', `./popper.js?${bsVersion}`).replace('./dom.js', `./dom.js?${bsVersion}`), { encoding: 'utf8', mode: 0o644 });
  await writeFile(resolve(outputFolder, file.replace('.js', '.min.js')), mini.code, { encoding: 'utf8', mode: 0o644 });
};

const build = async () => {
  // eslint-disable-next-line no-console
  console.log('Building ES6 Components...');

  const domImports = await readdir(resolve('node_modules/bootstrap', 'js/src/dom'));
  const utilImports = await readdir(resolve('node_modules/bootstrap', 'js/src/util'));

  const bundle = await rollup.rollup({
    input: resolve(inputFolder, 'index.es6.js'),
    plugins: [
      nodeResolve(),
      replace({
        preventAssignment: true,
        'process.env.NODE_ENV': '\'production\'',
      }),
      babel({
        exclude: 'node_modules/core-js/**',
        babelHelpers: 'bundled',
        babelrc: false,
        presets: [
          [
            '@babel/preset-env',
            {
              targets: {
                browsers: [
                  '> 1%',
                  'not op_mini all',
                ],
              },
            },
          ],
        ],
      }),
    ],
    manualChunks: {
      alert: ['build/media_source/vendor/bootstrap/js/alert.es6.js'],
      button: ['build/media_source/vendor/bootstrap/js/button.es6.js'],
      carousel: ['build/media_source/vendor/bootstrap/js/carousel.es6.js'],
      collapse: ['build/media_source/vendor/bootstrap/js/collapse.es6.js'],
      dropdown: ['build/media_source/vendor/bootstrap/js/dropdown.es6.js'],
      modal: ['build/media_source/vendor/bootstrap/js/modal.es6.js'],
      offcanvas: ['build/media_source/vendor/bootstrap/js/offcanvas.es6.js'],
      popover: ['build/media_source/vendor/bootstrap/js/popover.es6.js'],
      scrollspy: ['build/media_source/vendor/bootstrap/js/scrollspy.es6.js'],
      tab: ['build/media_source/vendor/bootstrap/js/tab.es6.js'],
      toast: ['build/media_source/vendor/bootstrap/js/toast.es6.js'],
      popper: ['@popperjs/core'],
      dom: [
        'node_modules/bootstrap/js/src/base-component.js',
        ...domImports.map((file) => `node_modules/bootstrap/js/src/dom/${file}`),
        ...utilImports.map((file) => `node_modules/bootstrap/js/src/util/${file}`),
      ],
    },
  });

  await bundle.write({
    format: 'es',
    sourcemap: false,
    dir: outputFolder,
    chunkFileNames: '[name].js',
  });

  // closes the bundle
  await bundle.close();
};

module.exports.bootstrapJs = async () => {
  rimraf.sync(resolve(outputFolder));

  try {
    await build(resolve(inputFolder, 'index.es6.js'));
    await unlink(resolve(outputFolder, 'index.es6.js'));
  } catch (error) {
    // eslint-disable-next-line no-console
    console.error(error);
    process.exit(1);
  }

  (await readdir(outputFolder)).forEach((file) => {
    tasks.push(createMinified(file));
  });

  return Promise.all(tasks).catch((er) => {
    // eslint-disable-next-line no-console
    console.log(er);
    process.exit(1);
  });
};
