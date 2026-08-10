module.exports = {
  apps: [{
    name: 'roombook',
    script: 'C:/xampp/php/php.exe',
    args: 'artisan serve --host=0.0.0.0 --port=8000',
    cwd: 'C:/Users/Administrator/Documents/GitHub/roombook',
    interpreter: 'none',
    autorestart: true,
    watch: false,
    max_restarts: 20,
    restart_delay: 2000
  }]
}
