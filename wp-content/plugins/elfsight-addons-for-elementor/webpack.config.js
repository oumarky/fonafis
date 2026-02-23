'use strict';

const webpack = require('webpack');
const path = require('path');

const CopyWebpackPlugin = require('copy-webpack-plugin');

const copyPath = process.env.COPY_PATH || null;

const copyright = () => {
  const year = (new Date()).getFullYear();

  return `\r\n\telfsight.com\r\n\r\n\tCopyright (c) ${year} Elfsight, LLC. ALL RIGHTS RESERVED\r\n`;
};

module.exports = {
  entry: './src/index.js',

  output: {
    filename: 'widget-control.js',
    path: path.resolve(__dirname, 'build'),
  },

  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: [{
          loader: 'babel-loader',
          options: {
            presets: [
              [
                '@babel/preset-env',
                {
                  useBuiltIns: 'usage',
                  corejs: 3
                }
              ]
            ]
          }
        }],
      },
      {
        test: /\.(css|styl)$/,
        use: [
          'style-loader',
          'css-loader',
          {
            loader: 'stylus-loader',
            options: {
              'include css': true
            }
          }
        ]
      },
    ]
  },

  plugins: [
    copyPath && (
      new CopyWebpackPlugin(
        [{
          from: '**',
          to: copyPath
        }],
        {
          copyUnmodified: false
        }
      )
    ),

    new webpack.BannerPlugin(copyright())
  ].filter(Boolean),
};
