const {
  existsSync, copy, writeFile, mkdir, mkdirs, ensureDir,
} = require('fs-extra');
const { dirname, join } = require('path');
const { codeMirror } = require('./exemptions/codemirror.es6.js');
const { tinyMCE } = require('./exemptions/tinymce.es6.js');

const RootPath = process.cwd();

/**
 * Find full path for package file.
 * Replacement for require.resolve(), as it is broken for packages with "exports" property.
 *
 * @param {string} relativePath Relative path to the file to resolve, in format packageName/file-name.js
 * @returns {string|boolean}
 */
const resolvePackageFile = (relativePath) => {
  for (let i = 0, l = module.paths.length; i < l; i += 1) {
    const path = module.paths[i];
    const fullPath = `${path}/${relativePath}`;
    if (existsSync(fullPath)) {
      return fullPath;
    }
  }

  return false;
};

/**
 *
 * @param {object} files    the object of files map, eg {"src.js": "js/src.js"}
 * @param {string} srcDir   the name of the package root dir
 * @param {string} destDir  the name of the Vendor destination dir
 *
 * @returns {Promise}
 */
const copyFilesTo = async (files, srcDir, destDir) => {
  const copyPromises = [];

  async function doTheCopy(source, dest) {
    await ensureDir(dirname(dest));
    await copy(source, dest, { preserveTimestamps: true });
  }

  // Copy each file
  // eslint-disable-next-line no-restricted-syntax,guard-for-in
  for (const srcFile in files) {
    copyPromises.push(doTheCopy(join(srcDir, srcFile), join(destDir, files[srcFile])));
  }

  return Promise.all(copyPromises);
};

/**
 * Main method that will resolve each vendor package
 *
 * @returns {Promise}
 */
const resolvePackage = async (vendor, packageName, mediaVendorPath, options, registry) => {
  const vendorName = vendor.name || packageName;
  const modulePathJson = resolvePackageFile(`${packageName}/package.json`);
  const modulePathRoot = dirname(modulePathJson);
  // eslint-disable-next-line global-require, import/no-dynamic-require
  const moduleOptions = require(modulePathJson);

  const promises = [];

  if (packageName === 'codemirror') {
    promises.push(codeMirror(packageName, moduleOptions.version));
  } else if (packageName === 'tinymce') {
    promises.push(tinyMCE(packageName, moduleOptions.version));
  } else {
    await mkdirs(join(mediaVendorPath, vendorName));

    ['js', 'css', 'filesExtra'].forEach((type) => {
      if (!vendor[type]) return;

      promises.push(
        copyFilesTo(vendor[type], modulePathRoot, join(mediaVendorPath, vendorName), type),
      );
    });
  }

  // Copy the license if existsSync
  if (options.settings.vendors[packageName].licenseFilename
  && await existsSync(`${join(RootPath, `node_modules/${packageName}`)}/${options.settings.vendors[packageName].licenseFilename}`)
  ) {
    const dest = join(mediaVendorPath, vendorName);
    await copy(
      `${join(RootPath, `node_modules/${packageName}`)}/${options.settings.vendors[packageName].licenseFilename}`,
      `${dest}/${options.settings.vendors[packageName].licenseFilename}`,
      { preserveTimestamps: true },
    );
  }

  await Promise.all(promises);

  // Add provided Assets to a registry, if any
  if (vendor.provideAssets && vendor.provideAssets.length) {
    vendor.provideAssets.forEach((assetInfo) => {
      const registryItemBase = {
        package: packageName,
        name: assetInfo.name || vendorName,
        version: moduleOptions.version,
        type: assetInfo.type,
      };

      const registryItem = Object.assign(assetInfo, registryItemBase);

      // Update path to file
      if (assetInfo.uri && (assetInfo.type === 'script' || assetInfo.type === 'style' || assetInfo.type === 'webcomponent')) {
        let itemPath = assetInfo.uri;

        // Check for external path
        if (itemPath.indexOf('http://') !== 0 && itemPath.indexOf('https://') !== 0 && itemPath.indexOf('//') !== 0) {
          itemPath = `vendor/${vendorName}/${itemPath}`;
        }

        registryItem.uri = itemPath;
      }

      registry.assets.push(registryItem);
    });
  }

  // eslint-disable-next-line no-console
  console.log(`${packageName} was updated.`);
};

/**
 * Main method that will copy all vendor files according to Joomla's specs
 *
 * @param options The options from setting.json
 *
 * @returns {Promise}
 */
module.exports.localisePackages = async (options) => {
  const mediaVendorPath = join(RootPath, 'media/vendor');
  const registry = {
    $schema: 'https://developer.joomla.org/schemas/json-schema/web_assets.json',
    name: options.name,
    version: options.version,
    description: options.description,
    license: options.license,
    assets: [],
  };
  const promises = [];

  if (!await existsSync(mediaVendorPath)) {
    await mkdir(mediaVendorPath, { recursive: true, mode: 0o755 });
  }

  // Loop to get some text for the package.json
  // eslint-disable-next-line guard-for-in, no-restricted-syntax
  for (const packageName in options.settings.vendors) {
    const vendor = options.settings.vendors[packageName];

    promises.push(resolvePackage(vendor, packageName, mediaVendorPath, options, registry));
  }

  await Promise.all(promises);

  // Write assets registry
  await writeFile(
    join(mediaVendorPath, 'joomla.asset.json'),
    JSON.stringify(registry, null, 2),
    { encoding: 'utf8', mode: 0o644 },
  );
};
