/**
 * This file is used to compile the assets from an UX package.
 */

const { parseArgs } = require('node:util');
const path = require('node:path');
const fs = require('node:fs');
const glob = require('glob');
const rollup = require('rollup');
const CleanCSS = require('clean-css');
const { getRollupConfiguration } = require('./rollup');

const args = parseArgs({
    allowPositionals: true,
    options: {
        watch: {
            type: 'boolean',
            description: 'Watch the source files for changes and rebuild when necessary.',
        },
    },
});

async function main() {
    const packageRoot = path.resolve(process.cwd(), args.positionals[0]);

    if (!fs.existsSync(packageRoot)) {
        console.error(`The package directory "${packageRoot}" does not exist.`);
        process.exit(1);
    }

    if (!fs.existsSync(path.join(packageRoot, 'package.json'))) {
        console.error(`The package directory "${packageRoot}" does not contain a package.json file.`);
        process.exit(1);
    }

    const packageData = require(path.join(packageRoot, 'package.json'));
    const packageName = packageData.name;
    const srcDir = path.join(packageRoot, 'src');
    const distDir = path.join(packageRoot, 'dist');

    if (!fs.existsSync(srcDir)) {
        console.error(`The package directory "${packageRoot}" does not contain a "src" directory.`);
        process.exit(1);
    }

    if (fs.existsSync(distDir)) {
        console.log(`Cleaning up the "${distDir}" directory...`);
        await fs.promises.rm(distDir, { recursive: true });
        await fs.promises.mkdir(distDir);
    }

    const inputScriptFiles = [
        ...glob.sync(path.join(srcDir, '*controller.ts')),
        ...(['@symfony/ux-react', '@symfony/ux-vue', '@symfony/ux-svelte'].includes(packageName)
            ? [path.join(srcDir, 'loader.ts'), path.join(srcDir, 'components.ts')]
            : []),
        ...(packageName === '@symfony/stimulus-bundle'
            ? [path.join(srcDir, 'loader.ts'), path.join(srcDir, 'controllers.ts')]
            : []),
    ];

    const inputStyleFile = packageData.config?.css_source;
    const buildCss = async () => {
        const inputStyleFileDist = inputStyleFile
            ? path.resolve(distDir, `${path.basename(inputStyleFile, '.css')}.min.css`)
            : undefined;
        if (!inputStyleFile) {
            return;
        }

        console.log('Minifying CSS...');
        const css = await fs.promises.readFile(inputStyleFile, 'utf-8');
        const minified = new CleanCSS().minify(css).styles;
        await fs.promises.writeFile(inputStyleFileDist, minified);
    };

    if (inputScriptFiles.length === 0) {
        console.error(
            `No input files found for package "${packageName}" (directory "${packageRoot}").\nEnsure you have at least a file matching the pattern "src/*_controller.ts", or manually specify input files in "${__filename}" file.`
        );
        process.exit(1);
    }

    const rollupConfig = getRollupConfiguration({ packageRoot, inputFiles: inputScriptFiles });

    if (args.values.watch) {
        console.log(
            `Watching for JavaScript${inputStyleFile ? ' and CSS' : ''} files modifications in "${srcDir}" directory...`
        );

        if (inputStyleFile) {
            rollupConfig.plugins = (rollupConfig.plugins || []).concat({
                name: 'watcher',
                buildStart() {
                    this.addWatchFile(inputStyleFile);
                },
            });
        }

        const watcher = rollup.watch(rollupConfig);
        watcher.on('event', ({ result }) => {
            if (result) {
                result.close();
            }
        });
        watcher.on('change', async (id, { event }) => {
            if (event === 'update') {
                console.log('Files were modified, rebuilding...');
            }

            if (inputStyleFile && id === inputStyleFile) {
                await buildCss();
            }
        });
    } else {
        console.log(`Building JavaScript files from ${packageName} package...`);
        const start = Date.now();

        const bundle = await rollup.rollup(rollupConfig);
        await bundle.write(rollupConfig.output);

        await buildCss();

        console.log(`Done in ${((Date.now() - start) / 1000).toFixed(3)} seconds.`);
    }
}

main();
