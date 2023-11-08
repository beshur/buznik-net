# Buznik.net Minimal

Buznik.net redesign (started 2016). Minimal for the client.

This is a repository for my home page.

## Building and development

Deploying via Grunt requires correct env variables (see `deploy-config-sample.sh`).

- `yarn --force`, well, to install dependencies
- `yarn build` to build assets and static html files, to test staff locally
- `yarn deploy` to build assets (includes `build` job) and to deploy them to remote
- `yarn start` to start watcher for development. #TODO: fix getting texts
