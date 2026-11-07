#!/bin/sh

# Default PORT to 10000 if not set
export PORT=${PORT:-10000}

# Remove default site config to prevent conflicts
rm -f /etc/nginx/sites-enabled/default

# Replace port 10000 with the runtime PORT in Nginx configuration
if [ -f /etc/nginx/conf.d/default.conf ]; then
    sed -i "s/listen 10000;/listen ${PORT};/g" /etc/nginx/conf.d/default.conf
fi

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
