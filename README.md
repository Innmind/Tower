# Tower

[![Build Status](https://github.com/Innmind/Tower/workflows/CI/badge.svg)](https://github.com/Innmind/Tower/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/Innmind/Tower/branch/develop/graph/badge.svg)](https://codecov.io/gh/Innmind/Tower)
[![Type Coverage](https://shepherd.dev/github/Innmind/Tower/coverage.svg)](https://shepherd.dev/github/Innmind/Tower)

This is a command line tool to deploy your code base with a new approach. Instead of building another tool sending a set of shell commands over ssh with a logic of point to point, Tower takes the approach of servers as nodes of a tree where from a node you trigger the _tower_ of sub-nodes.

The setup:

* one install of Tower per server
* the config to deploy the server is localised on it
* a _node*_ only knows its neighbours' servers

Advantages:

* One only knows what can be deployed on a server (not how)
* Keep the _know how to deploy_ on the server
* If the actions are updated, no one is impacted
* Easily cascade deployment (if a node retrieve sources from its parent)
* Nodes at the same level can be deployed in parrallel (via background jobs, loose direct output)
* Commands run locally, everything's logged so you can trace what/when an env is deployed

Drawbacks:

* A node need to know how to connect to its neighbours (if node is hacked the subtree is compromised) (specific to the `ssh` transport, `tcp` not affected)
* A node need to know how to connect to its parent to retrieve sources (lower the load on master repository) (only if you decide such a strategy)
* Cross relation between parent and neighbours

Example:
```
              A (you)
             / \
            /   \
           /     \
   (prod) B       C (staging)
         / \       \
        /   \       \
       /     \       \
      D       E       F
      |       |
      |       |
      G       H
```

Say here the tree below `B` is also for production, with this tool you could easily place the same Tower setup on the five servers and to deploy all of them, you would run a command like `tower ping B` and done!
Or if you want to only deploy `H`, just to be sure everythings deploying fine, connect to the machine and locally run `tower trigger` or add this as one of the neighbour on your local machine (`A`) and run `tower ping H`. Once again it's a tree, you can start from wherever you want.

But in a normal case you would just have `B` and `C` as neighbours of your machine.

To be really efficient (meaning not overloading your VCS server), setup neighbours to retrieve code from their parent. For instance, setup `B` and `C` with your VCS server as `git remote`; `D` and `E` have `B` as `git remote`, and so on...

*Note*: I talk about git here, but you're not forced to use it

*Note*: When cascading, if a neighbour fail to deploy, its subtree won't be deployed

Another use case would be you have a single server for your app, and other servers for related services required by your first server. You could imply, with this notion of tree, that every time you deploy your app it triggers the deployment of those related services (so everything is always up-to-date).

## Installation

```sh
composer global require innmind/tower
```

## Configuration

```yml
neighbours:
    _name_:
        url: ssh://example.com:80/path/to/config/on/neighbour/server.yml # scheme can be tcp or ssh, path only used via ssh
        tags: [foo, bar] # optional

exports: # optional
    - 'echo "ENV=value"' # contains list of env variables that will be available to each action

actions:
    - 'some bash command'
```

The `actions` set will be the commands run on you server when the server the configuration is on is pinged. Each actions will have as environment variables the ones built via the `exports` section, this array must be commands that will produce an output of the form `ENV=value`.

Finally the `neighbours` section is the list of the servers that will be pinged when the one the configuration is on is pinged. You can decide to ping a server either by `ssh` (leaving the server vulnerable to the outside world, but is the easiest) or by a `tcp` socket (allows to close all direct access to the machine but opens to a DOS attack).

## Usage

### Via `ssh`

On your machine configure a `tower.yml` file with the following:

```yaml
neighbours:
    gateway:
        url: ssh://gateway.com:22/path/to/configuration/on/gateway
```

Once done you can run `tower ping gateway` in this folder. This will connect to `gateway.com` via the port `22`, move to the folder `/path/to/configuration/on/gateway` and run the command `tower trigger`; you'll need to create a file `tower.yml` in the folder that will either describe the actions to do or the neighbours to ping.

### Via `tcp`

On your machine configure a `tower.yml` file with the following:

```yaml
neighbours:
    gateway:
        url: tcp://gateway.com:1337
```

On the server you need to create a similar file named `tower.yml` containing either the actions to run or the neighbours to ping; then in this folder run `tower listen 1337 -d`. This last command opens a tcp connection on port `1337` and waits for incoming pings.

Now you can run on your machine `tower ping gateway`.
