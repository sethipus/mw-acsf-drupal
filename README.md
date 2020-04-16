# Mars ACSF Platform

[![Build Status](https://marsdevteam.visualstudio.com/acsf/_apis/build/status/digital-experience-platform.acsf?branchName=master)](https://marsdevteam.visualstudio.com/acsf/_build/latest?definitionId=1894&branchName=master)

This repository contains the code to support the Mars ACSF project. 2020 stack.

# Getting Started

This project is based on BLT, an open-source project template and tool that enables building, testing, and deploying Drupal installations following Acquia Professional Services best practices. While this is one of many methodologies, it is our recommended methodology.

1. Review the [Required / Recommended Skills](https://docs.acquia.com/blt/developer/skills/) for working with a BLT project.
2. Ensure that your computer meets the minimum installation requirements (and then install the required applications). See the [BLT System Requirements](https://docs.acquia.com/blt/install/) and [DDev System Requirements](https://ddev.readthedocs.io/en/stable/#system-requirements).
3. Request access to organization that owns the project repo in GitHub (if needed).
4. Fork the project repository in GitHub.
5. Request access to the Acquia Cloud Environment for your project (if needed).
6. Setup a SSH key that can be used for GitHub and the Acquia Cloud (you CAN use the same key).
    1. [Setup GitHub SSH Keys](https://help.github.com/articles/adding-a-new-ssh-key-to-your-github-account/)
    2. [Setup Acquia Cloud SSH Keys](https://docs.acquia.com/acquia-cloud/ssh/generate)
7. Clone your forked repository. By default, Git names this "origin" on your local.
    ```
    $ git clone git@github.com:<account>/#GITHUB_PROJECT.git
    ```
8. To ensure that upstream changes to the parent repository may be tracked, add the upstream locally as well.
    ```
    $ git remote add upstream git@github.com:#GITHUB_ORG/#GITHUB_PROJECT.git
    ```

9. Update your the configuration located in the `/blt/blt.yml` file to match your site's needs. See [configuration files](#important-configuration-files) for other important configuration files.


----
# Setup Local Environment.

BLT provides an automation layer for testing, building, and launching Drupal 8 applications. For ease when updating codebase it is recommended to use [DDev](https://ddev.readthedocs.io/en/stable/).
1. Install Composer dependencies.
After you have forked, cloned the project and setup your blt.yml file install Composer Dependencies. (Warning: this can take some time based on internet speeds.)
    ```
    $ ddev composer install
    ```
2. Setup VM.
Setup the VM with the configuration from this repository's [configuration files](#important-configuration-files).

    ```
    $ ddev start
    ```

3. Setup a local blt alias.
If the blt alias is not available use this command outside and inside vagrant (one time only).
    ```
    $ ddev composer run-script `./vendor/bin/blt blt:init:shell-alias`
    ```

4. Setup a local Drupal site with an empty database.
Use BLT to setup the site with configuration.  If it is a multisite you can identify a specific site.
   ```
     $ ddev blt setup
    ```
   or
   ```
   $ ddev blt setup --site=[sitename]
   ```
   or you can use web UI.

6. Log into your site with drush.
Access the site and do necessary work at #LOCAL_DEV_URL by running the following commands.
    ```
    $ ddev drush uli
    ```
    If you used web UI to install your site, than you can go `/user` page.

---
## (Optional) Other Local Setup Steps

1. Set up frontend build and theme.
By default BLT sets up a site with the lightning profile and a cog base theme. You can choose your own profile before setup in the blt.yml file. If you do choose to use cog, see [Cog's documentation](https://github.com/acquia-pso/cog/blob/8.x-1.x/STARTERKIT/README.md#create-cog-sub-theme) for installation.
See [BLT's Frontend docs](https://docs.acquia.com/blt/developer/frontend/) to see how to automate the theme requirements and frontend tests.
After the initial theme setup you can configure `blt/blt.yml` to install and configure your frontend dependencies with `blt setup`.

2. Pull Files locally.
Use BLT to pull all files down from your Cloud environment.

   ```
   $ ddev blt drupal:sync:files
   ```

3. Sync the Cloud Database.
If you have an existing database you can use BLT to pull down the database from your Cloud environment.
   ```
   $ ddev blt sync
   ```

---

# Naming convention

* Commits - Use AB# mention to link from GitHub to Azure Boards work items. For example, AB#125 will link to work item ID 125.
    For example,
    ```
    AB#125: Carousel structure.
    ```
In addition, you can enter a commit or pull request message to transition the work item. The system will recognize fix, fixes, fixed and apply it to the #-mention item that follows. Some examples are provided as shown.

Examples:

Commit message |	Action
--- | ---
Fixed AB#123 |	Links and transitions the work item to the "done" state.
Adds a new feature, fixes AB#123. |	Links and transitions the work item to the "done" state.
Fixes AB#123, AB#124, and AB#126|	Links to Azure Boards work items 123, 124, and 126. Transitions only the first item, 123 to the "done" state.
Fixes AB#123, Fixes AB#124, Fixes AB#125 | Links to Azure Boards work items 123, 124, and 126. Transitions all items to the "done" state.
Fixing multiple bugs: issue #123 and user story AB#234 |	Links to GitHub issue 123 and Azure Boards work item 234. No transitions.

* Branches - A good way would be to add type of the branch and an issue number in a branch name.
    ```
    bugfix/AB#125-description
    feature/AB#125-description
    hotfix/AB#125-description
    ```

---

# Branching strategy

 Please, follow [gitflow] (https://www.atlassian.com/git/tutorials/comparing-workflows/gitflow-workflow) strategy. Feature branches should be made against "develop" and PRs should opened against upstream (from a forked repository) to the "develop" branch.

---

# Project links

* [Repository](https://github.mars.com/digital-experience-platform/acsf)
* [Azure Devops](https://marsdevteam.visualstudio.com/MarsExperiencePlatform)
* [Azure Pipelines](https://marsdevteam.visualstudio.com/acsf/_build)
* [Acquia Cloud](https://cloud.acquia.com/app/develop/applications/07d06816-f520-403b-a439-581f056b46d6)
* Acquia Site Factory UI:
    * [Live](https://www.mars.acsitefactory.com)
    * [Test](https://www.test-mars.acsitefactory.com)
    * [Dev](https://www.dev-mars.acsitefactory.com)

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
