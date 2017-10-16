const webpack = require('webpack'),
      path = require('path'),
      ExtractTextPlugin = require('extract-text-webpack-plugin');

module.exports = {
    entry: './app/entry.js',
    output: {
        filename: 'bundle.js'
    },
    devServer: {
        contentBase: './app',
        historyApiFallback: true,
        hot: true,
        inline: true
    },
    module: {
        rules: [
            /**
             * Transpile JavaScript and @ngInject
             */
            {
                test: /\.js$/,
                include: [ path.join(__dirname, './app') ],
                use: [
                    {
                        loader: 'babel-loader',
                        query: {
                            cacheDirectory: './webpack_cache/',
                            presets: [
                                ['es2015', {modules: false}],
                                'stage-1'
                            ],
                            plugins: ['transform-runtime', 'angularjs-annotate']
                        }
                    }
                ]
            },
            /**
             * Compile sass into css
             */
            {
                test: /\.s?css$/,
                use: ExtractTextPlugin.extract({
                    fallback: 'style-loader',
                    use: [
                        'css-loader',
                        {
                            loader: 'sass-loader',
                            options: {
                                includePaths: [ path.resolve(__dirname, './app/resources/styles') ]
                            }
                        }
                    ]
                })
            }
        ]
    },
    plugins: [
        new webpack.PrefetchPlugin('babel-runtime/core-js'),
        new webpack.PrefetchPlugin('./app/config/routes'),
        new webpack.HotModuleReplacementPlugin(),

        /**
         * Compile all sass files to a single css file
         */
        new ExtractTextPlugin('./style.css')
    ],
    resolve: {
        extensions: ['*', '.js', '.json', '.css', '.scss'],
        alias: {
            styles: path.resolve(__dirname, './app/resources/styles'),
            controllers: path.resolve(__dirname, './app/controllers')
        }
    }
}
