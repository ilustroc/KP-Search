FROM node:22-bookworm-slim

WORKDIR /var/www/html

COPY docker/dev/node/entrypoint.sh /usr/local/bin/kp-search-node-entrypoint

RUN chmod +x /usr/local/bin/kp-search-node-entrypoint

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
