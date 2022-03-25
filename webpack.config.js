const webpack = require('webpack');
const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .addPlugin(new webpack.ProvidePlugin({
        $: 'jquery',
        jQuery: 'jquery',
    }))
    .copyFiles([
        {
            from: './node_modules/ckeditor-youtube-plugin/youtube',
            to: '../bundles/fosckeditor/plugins/youtube/[path][name].[ext]',
            includeSubdirectories: true
        },
    ])

    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', [
            './assets/formFields.js',
            './assets/app.js',
            './assets/market.js',
            './assets/userEdit.js',
            './assets/modals.js',
            './assets/tab.js',

            './assets/market/search.js',
            './assets/market/noPageRefreshPagination.js',
            './assets/market/commoditiesList.js',
            './assets/market/itemsComponent.js',
            './assets/market/itemsActions.js',
            './assets/market/commodityForm.js',
            './assets/market/kitForm.js',
            './assets/market/notifications.js',
            './assets/profile/formCollectionWidgetHandler.js',

            './assets/smat/js/script.js',
            './assets/smat/css/style.css',
        ]
    )

    .addEntry('admin', [
            './assets/styles/admin.css',
            './assets/styles/admin/market/attributes.css',
            './assets/styles/admin/market/category.css',
            './assets/styles/admin/market/commodity.css',
            './assets/styles/front/var.css',
            './assets/admin.js',
            './assets/admin/marketAttributeForm.js',
            './assets/userEdit.js',
            './assets/admin/certificateCrud.js',
            './assets/admin/dynamicForm.js',
            './assets/admin/commodityForm.js',
        ]
    )

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())
    .enableVersioning(Encore.isDev())

    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    //.enableSassLoader()
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            config: './postcss.config.js',
        }
    })

// uncomment if you use TypeScript
//.enableTypeScriptLoader()

// uncomment if you use React
//.enableReactPreset()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
//.enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
//.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
