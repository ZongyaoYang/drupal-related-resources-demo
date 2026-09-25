# Related Resources Drupal Demo

A small Drupal 11 project built for a code walkthrough. A custom block shows published Resource nodes that share a Topic with the current Basic page.

## What it does

- Editors assign a term from the **Topics** vocabulary to Basic pages and Resources using `field_topic`.
- On a Basic page with a Topic, the **Related Resources** block lists up to five published Resources with that Topic.
- Drupal checks view access when selecting nodes. The block does not display on Resource pages or Basic pages without a Topic.
- The block varies its cache by route and invalidates the list when node content changes.

The implementation is in [`RelatedResourcesBlock.php`](web/modules/custom/related_resources/src/Plugin/Block/RelatedResourcesBlock.php). The module definition is in [`related_resources.info.yml`](web/modules/custom/related_resources/related_resources.info.yml). Site structure and Olivero block placement are exported in [`config/sync`](config/sync).

## Local setup

Prerequisites: [DDEV](https://ddev.com/get-started/) with a working container runtime, Git, and Composer (available through DDEV). The project uses Drupal 11, PHP 8.4, and MariaDB 11.8 in DDEV.

```bash
git clone https://github.com/ZongyaoYang/drupal-related-resources-demo.git
cd drupal-related-resources-demo
ddev start
ddev composer install
ddev drush site:install standard -y
```

Import the exported site configuration into this fresh installation. Drupal requires the site's UUID to match before a full configuration import:

```bash
ddev drush config:set system.site uuid 74968a24-dcc0-4245-a10f-6bf659beae7c -y
ddev drush config:import --source=/var/www/html/config/sync -y
ddev drush cache:rebuild
ddev launch
```

To obtain a one-time administrator sign-in link for your local site:

```bash
ddev drush user:login
```

**Sample content is not included in configuration exports.** Create it locally to try the feature:

1. Add two terms to **Structure → Taxonomy → Topics**: `Admissions` and `Financial Aid`.
2. Add a Basic page called `Admissions Guide` with Topic `Admissions` and publish it.
3. Add and publish two Resources with Topic `Admissions`: `Application Checklist` and `Application Deadlines`.
4. Add and publish a Resource with Topic `Financial Aid`, and add an unpublished Resource with Topic `Admissions`.
5. View `Admissions Guide`. The sidebar should show only the two published Admissions Resources. View a Resource page to confirm the block is absent.

## How the block works

The block checks the current route for a Basic page with a Topic, then queries nodes by content type, publication status, and matching Topic ID. It loads the matching nodes and renders their titles as links in an item list. The `getCacheContexts()` method varies the block by route; the `node_list` cache tag invalidates the list as node content changes.

## Known scope

This is a focused demonstration, not a content migration: taxonomy terms and sample nodes live in the local database. The query currently limits results to five without an explicit sort order.
