new TWTR.Widget({
  version: 2,
  type: 'profile',
  rpp: 3,
  interval: 6000,
  width: 250,
  height: 300,
  theme: {
    shell: {
      background: '#a6ca7d',
      color: '#ffffff'
    },
    tweets: {
      background: '#ffffff',
      color: '#385021',
      links: '#86b854'
    }
  },
  features: {
    scrollbar: false,
    loop: false,
    live: false,
    hashtags: true,
    timestamp: true,
    avatars: false,
    behavior: 'all'
  }
}).render().setUser('livecipher').start();
