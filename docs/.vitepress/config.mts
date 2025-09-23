import { defineConfig } from 'vitepress'
import { createHighlighter } from 'shiki'
import { execSync } from 'child_process'

import fs from 'fs';
import { dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url))
const latteLang = JSON.parse(fs.readFileSync(`${__dirname}/latte.tmLanguage.json`, 'utf8'))

// Get the latest git tag for version display
const getLatestVersion = () => {
  try {
    // Get the absolute latest tag regardless of current branch
    return execSync('git tag --sort=-version:refname | head -1', { encoding: 'utf8' }).trim()
  } catch {
    return 'dev'
  }
}

const version = getLatestVersion()

const latte = {
  'name': 'latte',
  'scopeName': 'text.latte',
  'aliases': ['latte'],
  'embeddedLangs': ['php', 'html', 'javascript', 'css'],
  ...latteLang
}

// https://vitepress.dev/reference/site-config
export default defineConfig({
  //base: '/cakephp-latte-view/',
  title: "LatteView for CakePHP",
  description: "A CakePHP plugin providing Latte template engine integration for CakePHP applications.",
  markdown: {
    languages: [latte, 'php'],
  },
  themeConfig: {
    search: {
      provider: 'local'
    },
    editLink: {
      pattern: 'https://github.com/josbeir/cakephp-latte-view/edit/main/docs/:path',
    },
    nav: [
      { text: 'Home', link: '/' },
      { text: 'Documentation', link: '/getting-started' },
      { text: `v${version}`, link: 'https://github.com/josbeir/cakephp-latte-view/releases' }
    ],

    sidebar: [
      {
        text: 'Documentation',
        items: [
          { text: 'Getting started', link: '/getting-started' },
          { text: 'Configuration', link: '/configuration' },
          { 
            text: 'Tags, filters & functions', 
            link: '/tags-filters-functions',
            collapsed: false,
            items: [
              { text: 'Functions', link: '/tags-filters-functions#custom-tags-functions-and-filters' },
              { text: 'Helpers', link: '/tags-filters-functions#helpers' },
              { text: 'Links', link: '/tags-filters-functions#links' },
              { text: 'Forms', link: '/tags-filters-functions#forms' },
              { text: 'I18n', link: '/tags-filters-functions#i18n' },
              { text: 'Filters', link: '/tags-filters-functions#filters' },
            ]
          },
          { text: 'Template parameters', link: '/template-parameters' },
          { text: 'Debugging', link: '/debugging' },
          { text: 'Console commands', link: '/commands' },
          {
            text: 'Extensions',
            collapsed: false,
            items: [
              { text: 'Frontend Extension', link: '/extensions/frontend' },
            ]
          },
          { text: 'Extending', link: '/extending' },
        ]
      }
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/josbeir/cakephp-latte-view' }
    ],

    footer: {
      message: 'Released under the <a href="https://github.com/josbeir/cakephp-latte-view/blob/main/LICENSE.md">MIT License</a>.',
    }    
  }
})
