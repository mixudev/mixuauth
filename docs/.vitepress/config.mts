import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'AI Auth System',
  description: 'Dokumentasi lengkap AI-Powered Authentication & Security System',
  lang: 'id-ID',
  ignoreDeadLinks: true,

  // Output ke /docs/.vitepress/dist
  outDir: './.vitepress/dist',

  head: [
    ['link', { rel: 'icon', href: '/favicon.ico' }],
    ['meta', { name: 'theme-color', content: '#6366f1' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:title', content: 'AI Auth System Docs' }],
    ['meta', { property: 'og:description', content: 'Dokumentasi AI-Powered Authentication System' }],
  ],

  themeConfig: {
    logo: '/logo.svg',

    nav: [
      { text: 'Beranda', link: '/' },
      { text: 'Dokumentasi', link: '/guide/' },
      { text: 'Referensi API', link: '/api/' },
      {
        text: 'v1.0.0',
        items: [
          { text: 'Repostiory (GitHub)', link: 'https://github.com' },
        ]
      }
    ],

    sidebar: {
      '/guide/': [
        {
          text: 'Persiapan Sistem',
          items: [
            { text: 'Pengenalan', link: '/guide/' },
            { text: 'Panduan Instalasi', link: '/guide/installation' },
            { text: 'Arsitektur Docker', link: '/guide/docker' },
            { text: 'Variabel Lingkungan', link: '/guide/environment' },
          ]
        },
        {
          text: 'Arsitektur Perangkat Lunak',
          items: [
            { text: 'Modularitas Laravel', link: '/architecture/modules' },
            { text: 'Mesin Deteksi Risiko (AI)', link: '/architecture/ai-engine' },
          ]
        }
      ],
      '/architecture/': [
        {
          text: 'Arsitektur Sistem',
          items: [
            { text: 'Modularitas Laravel', link: '/architecture/modules' },
            { text: 'Mesin Deteksi Risiko (AI)', link: '/architecture/ai-engine' },
          ]
        },
        {
          text: 'Infrastruktur',
          items: [
            { text: 'Arsitektur Docker', link: '/guide/docker' },
            { text: 'Panduan Instalasi', link: '/guide/installation' },
          ]
        }
      ],
      '/api/': [
        {
          text: 'Referensi API',
          items: [
            { text: 'Ikhtisar API', link: '/api/' },
            { text: 'Autentikasi & Otorisasi', link: '/api/auth' },
            { text: 'Mesin Risiko (FastAPI)', link: '/api/ai-risk' },
          ]
        }
      ]
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/mixudev/mixuauth' }
    ],

    footer: {
      message: 'AI Authentication System',
      copyright: 'Copyright © 2025 — Dokumentasi Internal'
    },

    search: {
      provider: 'local'
    },

    editLink: {
      pattern: 'https://github.com/mixudev/mixuauth/edit/main/docs/:path',
      text: 'Edit halaman ini'
    }
  }
})
