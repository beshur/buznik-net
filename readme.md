# Buznik.net Minimal

Buznik.net redesign (started 2016). Minimal for the client.

This is a repository for my home page.

## Building and development

Deploying via Grunt requires correct env variables (see `deploy-config-sample.sh`).

- install ruby
- `gem install sass`
- `npm install`, well, to install dependencies
- `npm run build` to build assets and static html files, to test staff locally
- `npm run deploy` to build assets (includes `build` job) and to deploy them to remote
- `npm start` to start watcher for development.
