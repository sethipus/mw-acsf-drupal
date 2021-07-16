# Mars ACSF Platform

[![Build Status](https://marsdevteam.visualstudio.com/MarsExperiencePlatform/_apis/build/status/New%20Stack?branchName=master)](https://marsdevteam.visualstudio.com/MarsExperiencePlatform/_build/latest?definitionId=2092&branchName=master)

This repository contains the code to support the Mars ACSF project - 2020 stack

  # Getting Started

  This project is based on BLT, an open-source project template and tool that enables building, testing, and deploying Drupal installations following Acquia Professional Services best practices. While this is one of many methodologies, it is our recommended methodology.

  * Review the [Required / Recommended Skills](https://docs.acquia.com/blt/developer/skills/) for working with a BLT project.

  * Ensure that your computer meets the minimum installation requirements (and then install the required applications). See the [BLT System Requirements](https://docs.acquia.com/blt/install/) and [DDev System Requirements](https://ddev.readthedocs.io/en/stable/#system-requirements).

  * Request access to organization that owns the project repo in GitHub (if needed).

  * Fork the project repository in GitHub.

  * Request access to the Acquia Cloud Environment for your project (if needed).

  * Setup a SSH key that can be used for GitHub and the Acquia Cloud (you CAN use the same key).

    * [Setup GitHub SSH Keys](https://help.github.com/articles/adding-a-new-ssh-key-to-your-github-account/)
    * [Setup Acquia Cloud SSH Keys](https://docs.acquia.com/acquia-cloud/ssh/generate)

  * Clone your forked repository. By default, Git names this "origin" on your local.

```
$ git clone git@github.mars.com:[your_login]/acsf.git
```

* To ensure that upstream changes to the parent repository may be tracked, add the upstream locally as well.

```
$ git remote add upstream git@github.mars.com:digital-experience-platform/acsf.git
```

----

# Setup Local Environment

BLT provides an automation layer for testing, building, and launching Drupal 8 applications. For ease when updating codebase it is recommended to use [DDev](https://ddev.readthedocs.io/en/stable/).

1. Navigate to https://github.mars.com/digital-experience-platform/acsf  and pull the project
2. Install all composer dependencies via command:
```
ddev composer install
```
note: this process might take around 20 minutes, drupal-core installation takes around 10 minutes, don’t abort the process, as it might seem that the process doesn’t respond

3. Sync your ssh key with ddev by running a command:
```
ddev auth ssh 
```

4. Sync your local ddev with the aliases by adjusting acsf\blt\local.blt.yml with adding the following lines to the end of the file:
```
drush:
 aliases:
   remote: local.01test
   local: self
   ci: self
 default_alias: '${drush.aliases.local}'
```

5. Create a file local.site.yml in directory *acsf\drush\sites*
And populate it with this content:
```
01dev:
 root: /var/www/html/mars.01dev/docroot
 uri: test.dev-mars.acsitefactory.com
 host: mars01dev.ssh.enterprise-g1.acquia-sites.com
 user: mars.01dev
0101qa:
 root: /var/www/html/mars.0101qa/docroot
 uri: demo.01qa-mars.acsitefactory.com
 host: mars0101qa.ssh.enterprise-g1.acquia-sites.com
 user: mars.0101qa
01test:
 root: /var/www/html/mars.01test/docroot
 uri: dove.test-mars.acsitefactory.com
 host: mars01test.ssh.enterprise-g1.acquia-sites.com
 user: mars.01test
01testmars:
 root: /var/www/html/mars.01test/docroot
 uri: translation.test-mars.acsitefactory.com
 host: staging-4529.enterprise-g1.hosting.acquia.com
 user: mars.01test
01live:
 root: /var/www/html/mars.01live/docroot
 uri: live.dovechocolate.com
 host: web-4527.enterprise-g1.hosting.acquia.com
 user: mars.01live
```

6. Enter the container itself to run the remaining commands, by running:
```
ddev ssh
```
(Note that each of these commands can be run from outside the container as well, by prefixing ddev to each command.)

7. To proceed with the sync run the command:
```
blt sync:all 
```
Note: it’s recommended to use VPN connection, so the process takes around 5 minutes, otherwise it might take around an hour.
After the process is done, there are some errors regarding the config – this is fine, proceed to the next steps.

8. To proceed with additional sync of images, etc. proceed with the command:
```
blt drupal:sync:files
```
Note: it’s recommended to use VPN connection, so the process takes around 5 minutes, otherwise it might take around an hour.

9. To create a local copy of all components, go to docroot/themes/custom/emulsifymars and run these commands:
```
npm i 
npm run build 
```

10. To get familiar with the existing components in Storybook, run this command:
```
npm run storybook
```

11. With all the things set up, under the root directory of the project run the command:
```
ddev start
```

# Switching between envs/sites
To change your local env for another env/site go through these steps:

1. Navigate to https://cloud.acquia.com/a/applications/all 
2. Go to Mars - ACSF Sites
3. Choose the environment (e.g. '01test')
4. Click Servers
5. Choose the required one and find its data (eg. mars.01test@staging-4529.enterprise-g1.hosting.acquia.com)
6. Navigate to the codebase of your local env
7. Adjust the file local.site.yml in directory acsf\drush\sites\ with the following data:
```
-name (unique identifier for your local env, eg. O2twix)
-root: /var/www/html/mars.01test/docroot (01test might be changed to any value obtained from the step 5)
-uri:  twixus.test-mars.acsitefactory.com (for this uri go the test environment, choose Domain Management and in the search filed look for a required brand )
-host: staging-4529.enterprise-g1.hosting.acquia.com (copy the last part from the step 5)
-user: mars.01test (copy the first part from the step 5)
```
8. Navigate to root folder/ blt/local.blt.yml and apply the change
```
drush:
 aliases:
   remote: local.o2twixtest (after local goes your unique name from the step 7)
```
9. Clear the cache of the previous env by running commands:
```
ddev ssh
ddev drush cr
ddev drush cr
```
10. To sync styles for this new version run the command under the root directory:
```
ddev blt sync:all
```
11. To sync all files for this new version run the command under the root directory:
```
ddev blt drupal:sync:files
```
12. After every sync unregister your site from Acquia Content Hub
```
ddev drush ach-disconnect
```
and remove GTM configuration on page
```
/admin/config/system/gtm
```
13. To run your local env with new content run the command:
```
ddev start 
```
14. Access the Drupal admin interface to make changes
```
ddev drush uli
```
Note: This command will return a link that may not work. If this issue occurs, prefix the port number to the link that was returned when running ddev start (e.g. https://mars.ddev.site:8443/user/reset/1/1614847457/cbvsKM1vTJuhT2Z-6oq6RhZG7z7Zi6fRzTCeAb46RPw/login )
15. Setup Acquia Solr connection from local. Command below will add settings to allow index content from local to Solr instance on Acquia side:
```
echo '$config["acquia_search.settings"]["disable_auto_read_only"] = TRUE;' >> docroot/sites/default/settings.ddev.php
```
If Drupal site not subscribed to Solr, try to uninstall Acquia Connector module together with Acquia Search and install them back again.

# Coding Standards

Please be sure that you are familiar with Drupal coding standards when committing to the repository.
Ensure that you are familiar and up to date on [Drupal Coding Standards](https://www.drupal.org/docs/develop/standards).

Be sure to use [Drupal javascript coding standards](https://www.drupal.org/docs/develop/standards/javascript/javascript-coding-standards) as well.

`blt setup` ensures that a general suite of validation checks are run, including PHPCS Drupal Coding Standard rules, but it is highly suggested that you run code linting within your IDE.

For information on setup, see:

* [PHPStorm and general info](https://www.drupal.org/docs/8/modules/code-review-module/installing-coder-sniffer#s-ide-and-editor-configuration)
* [Sublime, Atom, Komodo, VS Code, and others](https://www.drupal.org/docs/8/modules/code-review-module/installing-drupal-code-sniffer-on-vim-sublime-text-visual-studio)

If you are not familiar with basic engineering principals such as those listed below, please review their respective links:
* [DRY](http://en.wikipedia.org/wiki/Don%27t_repeat_yourself)
* [KISS](https://en.wikipedia.org/wiki/KISS_principle)
* [SOLID](https://en.wikipedia.org/wiki/SOLID)

Following best practices for engineering your solution and code syntax ensures that we have a consistent, readable, and secure codebase that will be easily maintainable throughout the life of the platform.

---

# Code Collaboration

* Understand the requirements or bug fix clearly for which the PR raised. Ensure you have access to DevOps ticket where the User story or Bug fix is maintained.

* Ensure the modules present in the PR are reviewed and approved by MARS EA & platform teams

## Pull code from the upstream develop branch and create a new branch:

```
git checkout develop
git pull upstream develop
git checkout –b <my-new-feature>
```

## Work on your feature, stage your changes, and commit changes:

```
git add <file names>
git commit –m “AB#1234 <description of your change>”
```

### Commit Messages

Use of AB#XXX in your commit message is required and creates a link from GitHub to Azure Boards work items. For example, AB#125 will link to Azure work item ID 125.

**Example:**

```
AB#125: Carousel structure.
```
In addition, you can enter a commit or pull request message to transition the work item. The system will recognize `fix`, `fixes`, `fixed` and apply it to the #-mention item that follows. Some detailed examples of transitions are provided below.

**Transitions:**

Commit message                                         |	Action
------------------------------------------------------ | -------
Fixed AB#123                                           |	Links and transitions the work item to the "done" state.
Adds a new feature, fixes AB#123.                      |	Links and transitions the work item to the "done" state.
Fixes AB#123, AB#124, and AB#126                       |	Links to Azure Boards work items 123, 124, and 126. Transitions only the first item, 123 to the "done" state.
Fixes AB#123, Fixes AB#124, Fixes AB#125               | Links to Azure Boards work items 123, 124, and 126. Transitions all items to the "done" state.
Fixing multiple bugs: issue #123 and user story AB#234 |	Links to GitHub issue 123 and Azure Boards work item 234. No transitions.

### Committing to PRs

* Make multiple commits if necessary, do not include changes to different features or components in one Pull Request, this increases the difficulty of testing.

* Push your branch to a fork of the repository “digital-experience-platform/acsf repository.
  ```
  git push <my repository> <my-new-feature>
  ```

  **Example:**

  Remote URL: https://github.mars.com/ted/acsf
  ```
   git push ted my-new-feature
  ```

* Ensure all validation test run on push, they should look like:

```
Executing .git/hooks/pre-push...
> validate
> tests:composer:validate
Validating composer.json and composer.lock...
[ExecStack] composer validate --no-check-all --ansi
[ExecStack] Running composer validate --no-check-all --ansi in /Users/ted_slesinski/repos/acsf
./composer.json is valid, but with a few warnings
See https://getcomposer.org/doc/04-schema.md for details on the schema
License "GPL-2.0+" is a deprecated SPDX license identifier, use "GPL-2.0-or-later" instead
The package "behat/mink-selenium2-driver" is pointing to a commit-ref, this is bad practice and can cause unforeseen issues.
[ExecStack] Done in 0.811s
> tests:php:lint
Linting PHP files...
Iterating over fileset files.php.custom.modules...
Iterating over fileset files.php.custom.themes...
> tests:phpcs:sniff:all
[ExecStack] '/Users/ted_slesinski/repos/acsf/vendor/bin/phpcs'
............................................................   60 / 2090 (3%)
...
..................................................           2090 / 2090 (100%)
```
* Open a PR to: digital-experience-platform/acsf:develop

* Ensure all CI Tests are passing and assign to a reviewer.

---

# Code Review

* Ensure the modules present in the PR are reviewed and approved by MARS EA & platform teams

  * To do this, check if there are changes to the `composer.json` file.

  * Modules additions are added after the require key:
  ```
  "require": {
      "php": ">=7.2",
      "acquia/acsf-tools": "dev-9.x-dev",
    ...
  ```

* Ensure the pull request is fully tested with the following process:
  * Verify the PR for Ticket reference and check that all changes are included.

  * Pull the PR to the local machine for validation .

  * Add the remote  alias, if necessary (You can review added remotes with the command: `git remote`):
  ```
  git remote add <requestor username> <requestor's repository>
  ```

    **Example:**

    Remote URL: https://github.mars.com/ted/acsf
    ```
     git remote add ted git@github.mars.com:ted/acsf.git
    ```

  * Pull the requestor fork changes into the local machine:
  ```
  git fetch <alias> 
  git checkout <pr branch>
  ```
  * Install dependencies and run BLT Validation:
  ```
  composer install 
  blt validate
  ```
  * Import configuration

  * Test from local website

* if there are unresolved issues, request changes and reassign or approve the PR if everything works as expected. 

---

# Releases

Releases should created at:

https://github.mars.com/digital-experience-platform/acsf/releases/new

**Tag version** will be in the format of the approved tagging convention: TBD

Target should be: **master**

Release should have a title and a description including all features of the release.

---

# Project links

* [Repository](https://github.mars.com/digital-experience-platform/acsf)
* [Azure Devops](https://marsdevteam.visualstudio.com/MarsExperiencePlatform)
* [Azure Pipelines](https://marsdevteam.visualstudio.com/MarsExperiencePlatform/_build)
* [Acquia Cloud](https://cloud.acquia.com/app/develop/applications/f9d8b9b4-8a11-4c11-ae1b-06816b04aa57)
* Acquia Site Factory UI:
  * [Live](https://www.marsinc.acsitefactory.com)
  * [Test](https://www.test-marsinc.acsitefactory.com)
  * [Dev](https://www.dev-marsinc.acsitefactory.com)

---

# Resources

Additional [BLT documentation](https://docs.acquia.com/blt/) may be useful. You may also access a list of BLT commands by running this:

```
$ ddev blt
```

Note the following properties of this project:
* Primary development branch: #GIT_PRIMARY_DEV_BRANCH
* Local environment: #LOCAL_DEV_SITE_ALIAS
* Local site URL: #LOCAL_DEV_URL

## Working With a BLT Project

BLT projects are designed to instill software development best practices (including git workflows).

Our BLT Developer documentation includes an [example workflow](https://docs.acquia.com/blt/developer/dev-workflow/).

### Important Configuration Files

BLT uses a number of configuration (`.yml` or `.json`) files to define and customize behaviors. Some examples of these are:

* `blt/blt.yml` (formerly blt/project.yml prior to BLT 9.x)
* `blt/local.blt.yml` (local only specific blt configuration)
* `drush/sites` (contains Drush aliases for this project)
* `composer.json` (includes required components, including Drupal Modules, for this project)
