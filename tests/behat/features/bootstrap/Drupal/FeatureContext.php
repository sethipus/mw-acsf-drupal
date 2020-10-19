<?php
namespace Drupal;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use PHPUnit\Framework\Assert;
use Drupal\user\Entity\Role;
use Behat\Gherkin\Node\TableNode;
use Drupal\node\Entity\NodeType;
use Drupal\media\Entity\MediaType;
use Drupal\block_content\Entity\BlockContentType;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext
{

    public static $loginUrl;

    /**
     * Every scenario gets its own context instance.
     *
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
      //if (strcmp(shell_exec('drush uli'), "") == 0) {
        //echo "Installing drush/drush:10.2.2...";
        //shell_exec('composer global require drush/drush:10.2.2');
        //shell_exec('export PATH="$HOME/.composer/vendor/bin:$PATH"');

        //shell_exec('sudo apt-get update && sudo apt-get upgrade');
        //shell_exec('sudo apt-get install git');
        //shell_exec('sudo apt-get install php-xml, zip');
        //shell_exec('curl -sS https://getcomposer.org/installer | php');
        //shell_exec('sudo mv composer.phar /usr/local/bin/composer');

        //shell_exec('sudo ln -s /usr/local/bin/composer /usr/bin/composer');
        //shell_exec('sudo git clone https://github.com/drush-ops/drush.git /usr/local/src/drush');
        //shell_exec('cd /usr/local/src/drush');
        //shell_exec('sudo git checkout 10.2.2');
        //shell_exec('sudo ln -s /usr/local/src/drush/drush /usr/bin/drush');
        //shell_exec('sudo composer install');
        //echo "Drush version is: " . shell_exec('drush --version');
        //$isComposerInstalled = TRUE;
      //}
    }

    /**
     * Entity Functions.
     */

    /**
     * @Given I login into Drupal
     *
     * Examples:
     * Given I login into Drupal
     *
     * @throws \Exception;
     */
    public function loginDrupal()
    {
        shell_exec('sudo pkill chrome');
        //echo "pwd: " . shell_exec('pwd') . "\n";

        //echo "which drush is: " . shell_exec('which drush') . "\n";

        //echo "ls: " . shell_exec('ls') . "\n";
        //echo "cd ..; ls: " . shell_exec('cd ..; ls') . "\n";
        //echo "cd ../drush; ls: " . shell_exec('cd ../drush; ls') . "\n";

        echo "this login url: " . $this->loginUrl . "\n";

        //$session = $this->getSession();
        //$session->visit($domain . "/admin");
        //$code = $session->getStatusCode();

          echo "cd ../vendor/bin; ls: " . shell_exec('cd ../vendor/bin; ls') . "\n";
          echo "cd ../vendor/drush/drush; ls: " . shell_exec('cd ../vendor/drush/drush; ls') . "\n";
          $domain = $this->getMinkParameter('base_url');
          echo "domain: " . $domain . "\n";

          $uli = preg_replace('/\n$/', '', shell_exec('cd ../vendor/bin; ./drush uli --uri=' .$domain));
          echo "uli: " . $uli . "\n";
          $uli = str_replace('https://mars.ddev.site:8443', 'http://mars.ddev.site:8080', $uli);
          echo "http uli: " . $uli . "\n";;

          $this->getSession()->visit($uli);
          $this->loginUrl = $uli;
    }

    /**
     * @When I login into Drupal with used url
     *
     * Examples:
     * When I login into Drupal with used url
     *
     * @throws \Exception;
     */
    public function loginDrupalWithUsedUrl()
    {
        $this->visitPath($this->loginUrl);
    }

    /**
     * @When I edit added content
     *
     * Example: When I edit added content
     * Example: And I edit added content
     *
     * @throws \Exception;
     */
    public function iEditAddedContent()
    {
        $this->getSession()
            ->getPage()
            ->find('css', 'article button')
            ->click();
        $this->getSession()
            ->getPage()
            ->find('css', 'li.entitynodeedit-form > a')
            ->click();
    }

    /**
     * @When I press the :arg1 section of added content
     *
     * Example: When I press the "Edit" section of added content
     * Example: And I press the "Edit" section of added content
     *
     * @throws \Exception;
     */
    public function iPressEditSectionOfAddedContent($section)
    {
        $this->getSession()
            ->getDriver()
            ->click("//nav[contains(@class, 'tabs-wrapper')]//a[text() = '" . $section . "']");
    }

    /**
     * @When I fill item :arg1 into subqueue FAQ queue
     *
     * Example: When I fill item "Test QA Blurb1" into subqueue FAQ queue
     * Example: And I fill item "Test QA Blurb1" into subqueue FAQ queue
     *
     * @throws \Exception;
     */
    public function iFillItemSubqueueFaqQueue($text)
    {
        $this->getSession()
            ->getPage()
            ->fillField("items[add_more][new_item][target_id]", $text);
    }

    /**
     * @When I check content with title :arg1
     *
     * Example: When I check content with title "TestBasicPageTitle"
     * Example: And I check content with title "TestBasicPageTitle"
     *
     * @throws \Exception;
     */
    public function iCheckContentWithTitle($title)
    {
        $this->getSession()
            ->getDriver()
            ->click("//a[(text() = '" . $title . "')]/ancestor::tr//input[contains(@class, 'form-checkbox')]");
    }

    /**
     * @Then /^I wait for the ajax response$/
     *
     * Example: And I wait for the ajax response
     * Example: Then I wait for the ajax response
     *
     * @throws \Exception;
     */
    public function iWaitForTheAjaxResponse()
    {
        $this->getSession()
            ->wait(5000, '(0 === jQuery.active)');
    }

    /**
     * Checks, that element with specified XPATH exists on page
     * Example: Then I should see a "//body" xpath element
     * Example: And I should see a "//body" xpath element
     *
     * @Then /^(?:|I )should see an? "(?P<element>[^"]*)" xpath element$/
     */
    public function assertXpathElementOnPage($element)
    {
        $this->assertSession()
            ->elementExists('xpath', $element);
    }

    /**
     * Checks, that XPATH element exists and click on it
     * Example: Then I click on "//body" xpath element
     * Example: And I click on "//body" xpath element
     *
     * @Then /^(?:|I )click on "(?P<element>[^"]*)" xpath element$/
     */
    public function clickXpathElementOnPage($element)
    {
        $this->assertSession()
            ->elementExists('xpath', $element)->click();
    }

    /**
     * Expands area with the given name
     * Example: Then I expand "Relations" area
     * Example: And I expand "Relations" area
     *
     * @Then /^(?:|I )expand "(?P<areaName>[^"]*)" area/
     */
    public function expandArea($areaName)
    {
        $this->getSession()
            ->getDriver()
            ->click("//*[@aria-expanded='false' and text()='" . $areaName . "']");
    }

    /**
     * Click on the link element which href attribute contains the specified value
     * Example: Then I click link which contains "edit?destination=/admin/structure/taxonomy/manage/mars_flavor/overview"
     * Example: And I click link which contains "edit?destination=/admin/structure/taxonomy/manage/mars_flavor/overview"
     *
     * @Then /^(?:|I )click link which contains "(?P<value>[^"]*)"/
     */
    public function clickLink($value)
    {
        $xpath = "//a[contains(@href, '" . $value . "')]";
        $this->getSession()
            ->getDriver()
            ->click($xpath);
    }

    /**
     * Sleep n seconds
     * Example: Then I sleep "5" seconds
     * Example: And I sleep "5" seconds
     *
     * @Then /^(?:|I )sleep "(?P<value>[^"]*)" seconds/
     */
    public function sleep($seconds)
    {
        sleep($seconds);
    }

    /**
     * Loads page by href of link with text
     * Example: When I load page by link with text "MARS: Contact Help Banner"
     * Example: And I load page by link with text "MARS: Contact Help Banner"
     *
     * @When I load page by link with text :arg1
     */
    public function loadPageByLinkWithText($text)
    {
        $address = $this->getSession()
            ->getPage()
            ->find('xpath', "//a[(text() = '" . $text . "')]")->getAttribute('href');
        $this->visitPath($address);
    }

    /**
     * @Given the :arg1 content type exists
     *
     * Examples:
     * Given the "blog" content type exists
     *
     * @throws \Exception;
     */
    public function contentTypeExists($string)
    {
        $node_type = NodeType::load($string);
        if (empty($node_type))
        {
            throw new \Exception('Content type ' . $string . ' does not exist.');
        }
    }

    /**
     * @Given the :arg1 media type exists
     *
     * Examples:
     * Given the "image" media type exists
     *
     * @throws \Exception;
     */
    public function mediaTypeExists($string)
    {
        $media_type = MediaType::load($string);
        if (empty($media_type))
        {
            throw new \Exception('Media type ' . $string . ' does not exist.');
        }
    }

    /**
     * @Given the :arg1 block_content type exists
     *
     * Examples:
     * Given the "hero" block_content type exists
     *
     * @throws \Exception;
     */
    public function blockTypeExists($string)
    {
        $block_type = BlockContentType::load($string);
        if (empty($block_type))
        {
            throw new \Exception('Block Content type ' . $string . ' does not exist.');
        }
    }

    /**
     * Check for presence of a field on a bundle.
     *
     * Examples:
     * Then the field "body" is present on the "blog" "node" type
     * Then the field "body" is present on the "hero" "block_content" type
     * Then the field "body" is present on the "slide" "paragraph" type
     * Then the field "body" is present on the "image" "media" type
     * Then the field "body" is present on the "tag" "vocabulary" type
     *
     * @Then the field :arg1 is present on the :arg2 :arg3 type
     */
    public function isField($field_name, $bundle, $entity)
    {
        $bundle_fields = \Drupal::getContainer()->get('entity_field.manager')
            ->getFieldDefinitions($entity, $bundle);
        if (empty($bundle_fields[$field_name]))
        {
            Assert::assertEmpty($bundle_fields, $field_name . ' is not present on the ' . $entity . " " . $bundle);
        }
    }

    /**
     * Check if a present field is required on a bundle.
     *
     * Examples:
     * Then the field "body" is required on the "blog" "node" type
     * Then the field "body" is required on the "hero" "block_content" type
     * Then the field "body" is required on the "slide" "paragraph" type
     * Then the field "body" is required on the "image" "media" type
     * Then the field "body" is required on the "tag" "vocabulary" type
     *
     * @Then the field :arg1 is required on the :arg2 :arg3 type
     */
    public function isRequiredField($field_name, $bundle, $entity)
    {
        $bundle_fields = \Drupal::getContainer()->get('entity_field.manager')
            ->getFieldDefinitions($entity, $bundle);
        $field_definition = $bundle_fields[$field_name];
        $setting = $field_definition->isRequired();
        Assert::assertNotEmpty($setting, 'Field ' . $field_name . ' is not required.');
    }

    /**
     * Check if a present field is not required on a bundle.
     *
     * Examples:
     * Then the field "body" is not required on the "blog" "node" type
     * Then the field "body" is not required on the "hero" "block_content" type
     * Then the field "body" is not required on the "slide" "paragraph" type
     * Then the field "body" is not required on the "image" "media" type
     * Then the field "body" is not required on the "tag" "vocabulary" type
     *
     * @Then the field :arg1 is not required on the :arg2 :arg3 type
     */
    public function isNotRequiredField($field_name, $bundle, $entity)
    {
        $bundle_fields = \Drupal::getContainer()->get('entity_field.manager')
            ->getFieldDefinitions($entity, $bundle);
        $field_definition = $bundle_fields[$field_name];
        $setting = $field_definition->isRequired();
        Assert::assertNotEmpty($setting, 'Field ' . $field_name . ' is not required.');
    }

    /**
     * Check a reference field's target bundle.
     *
     * Examples:
     * Then the field "categories" on the "blog" "node" type allows references to "categories"
     * Then the field "categories" on the "hero" "block_content" type allows references to "categories"
     * Then the field "categories" on the "slide" "paragraph" type allows references to "categories"
     * Then the field "categories" on the "image" "media" type allows references to "categories"
     * Then the field "categories" on the "tag" "vocabulary" type allows references to "categories"
     *
     * @Then the field :arg1 on the :arg2 :arg3 type should allow references to :arg4
     */
    public function fieldAllowsEntityReferences($field_name, $bundle, $entity, $reference_bundle)
    {
        $bundle_fields = \Drupal::getContainer()->get('entity_field.manager')
            ->getFieldDefinitions($entity, $bundle);
        $field_definition = $bundle_fields[$field_name];
        $settings = $field_definition->getSettings();
        if (!empty($settings['handler_settings']['target_bundles']))
        {
            $target_bundles = $settings['handler_settings']['target_bundles'];
        }
        elseif (!empty($settings['handler_settings']['target_bundles_drag_drop']))
        {
            $target_bundles = $settings['handler_settings']['target_bundles_drag_drop'];
            foreach ($target_bundles as $key => $bundle)
            {
                $target_bundles[$key] = $key;
            }
        }
        else
        {
            return false;
        }
        Assert::assertContains(trim($reference_bundle) , $target_bundles, $field_name . ' does not allow references to ' . trim($reference_bundle) . ' content');
    }

    /**
     * Check if a particular field is visible on a particular entity display mode
     *
     * Examples:
     * Then the display "teaser" on the "blog" "node" type should display the "field_status" field
     * Then the display "teaser" on the "hero" "block_content" type should display the "field_status" field
     * Then the display "teaser" on the "slide" "paragraph" type should display the "field_status" field
     * Then the display "teaser" on the "image" "media" type should display the "field_status" field
     * Then the display "teaser" on the "tag" "vocabulary" type should display the "field_status" field
     *
     * @Then the display :arg1 on the :arg2 :arg3 type should display the :arg4 field
     *
     */
    public function nodeFieldVisibile($display, $bundle, $entity, $field_name)
    {
        $storage = \Drupal::entityTypeManager()->getStorage('entity_view_display');
        $view_display = $storage->load("$entity.$bundle.$display");
        $component = $view_display->getComponent($field_name);
        Assert::assertContains('content', $component['region']);
    }

    /**
     * Check if a particular field is not visible on a particular entity display mode
     *
     * Examples:
     * Then the display "teaser" on the "blog" "node" type should not display the "field_status" field
     * Then the display "teaser" on the "hero" "block_content" type should not display the "field_status" field
     * Then the display "teaser" on the "slide" "paragraph" type should not display the "field_status" field
     * Then the display "teaser" on the "image" "media" type should not display the "field_status" field
     * Then the display "teaser" on the "tag" "vocabulary" type should not display the "field_status" field
     *
     * @Then the display :arg1 on the :arg2 :arg3 type should not display the :arg4 field
     */
    public function nodeFieldNotVisibile($display, $bundle, $entity, $field_name)
    {
        $storage = \Drupal::entityTypeManager()->getStorage('entity_view_display');
        $view_display = $storage->load("$entity.$bundle.$display");
        $component = $view_display->get('hidden');
        Assert::assertEquals(true, $component[$field_name]);
    }

    /**
     *
     * Checks the cardinality of a field on a given bundle.
     *
     * Examples:
     * The field "categories" on the "node" bundle has a cardinality of "-1".
     * The field "categories" on the "block_content" bundle has a cardinality of "-1".
     * The field "categories" on the "paragraph" bundle has a cardinality of "-1".
     * The field "categories" on the "media" bundle has a cardinality of "-1".
     * The field "categories" on the "vocabulary" bundle has a cardinality of "-1".
     *
     * @Then the field :arg1 on the :arg2 bundle has a cardinality of :arg3
     *
     */
    public function checkCardinality($field, $bundle, $cardinality)
    {
        $config = $this->checkCardinality($bundle, $field, $cardinality);
        Assert::assertEquals($cardinality, $config, "The $field does not have the correct cardinality. It should be $cardinality but in reality is $config.");
    }

    /**
     * Checks the maximum length of a field on a given bundle.
     *
     * Examples:
     * The field "title" on the "node" bundle has a maximum length of "100"
     * The field "title" on the "block_content" bundle has a maximum length of "100"
     * The field "title" on the "paragraph" bundle has a maximum length of "100"
     * The field "title" on the "media" bundle has a maximum length of "100"
     * The field "title" on the "vocabulary" bundle has a maximum length of "100"
     *
     * @Then the field :arg1 on the :arg2 :arg3 type has a maximum length of :arg4
     */
    public function nodeFieldLengthLimit($field_name, $bundle, $entity, $length)
    {
        $bundle_fields = \Drupal::getContainer()->get('entity_field.manager')
            ->getFieldDefinitions($entity, $bundle);
        $field_definition = $bundle_fields[$field_name];
        $settings = $field_definition->getSettings();
        Assert::assertEquals($length, $settings['max_length']);
    }

    /**
     * @Then I visit the last created :arg1
     *
     * Locate the last created node or media and then redirect to it.
     *
     * Examples:
     * Then I visit the last created node.
     * Then I visit the last created media.
     */
    public function iVisitLatestContent($bundle)
    {
        switch ($bundle)
        {
            case "node":
            default:
                $id = $this->getLastCreatedNode();
            break;
            case "media":
                $id = $this->getLastCreatedMedia();
            break;
        }
        $this->getSession()
            ->visit($this->locatePath("$bundle/$id"));

    }

    /**
     * Permission Functions.
     */

    /**
     * @Given the following roles have these permissions:
     *
     * Examples:
     * Given the following roles have these permissions:
     * | role                  | permission                                  |
     * | anonymous             | access content                              |
     * | authenticated         | access content                              |
     * | reviewer              | use content_workflow transition ready_ready |
     */
    public function roleUserPermissions(TableNode $rolesTable)
    {
        foreach ($rolesTable as $rolePermission)
        {
            $role = $rolePermission['role'];
            $permission = $rolePermission['permission'];
            $this->roleHasPermission($role, $permission);
        }
    }

    /**
     * @Given the following roles do not have these permissions:
     *
     * Examples:
     * Given the following roles do not have these permissions:
     * | role                  | permission                                  |
     * | anonymous             | access content                              |
     * | authenticated         | access content                              |
     * | reviewer              | use content_workflow transition ready_ready |
     */
    public function roleUserPermissionsNot(TableNode $rolesTable)
    {
        foreach ($rolesTable as $rolePermission)
        {
            $role = $rolePermission['role'];
            $permission = $rolePermission['permission'];
            $this->roleDoesNotHavePermission($role, $permission);
        }
    }

    /**
     * @Then :arg1 role has permission to :arg2
     *
     * Examples:
     * Then the "reviewer" role has permission to "access content"
     */
    public function checkRolePermissions($role, $permission)
    {
        $this->roleHasPermission($role, $permission);
    }

    /**
     * @Then :arg1 role does not have permission to :arg2
     *
     * Examples:
     * Then the "reviewer" role does not have permission to "access content"
     */
    public function checkRoleDoesNotHavePermission($role, $permission)
    {
        $this->roleDoesNotHavePermission($role, $permission);
    }

    /**
     * Checks that only valid roles have permission to execute certain content actions
     *
     * Examples:
     * | role                    | permission |
     * | author           	     | create     |
     * | author           	     | edit own   |
     * | editor           	     | edit any   |
     * | content_administrator   | create     |
     * | content_administrator	 | edit own   |
     * | content_administrator	 | edit any   |
     *
     * @Given that only the following roles have content permissions for the :arg1 content type:
     *
     */
    public function roleOnlyContentPermissions($bundle, TableNode $rolesTable)
    {
        $allowed_roles = array();
        foreach ($rolesTable as $rolePermission)
        {
            $role = $rolePermission['role'];
            $permission = $rolePermission['permission'] . ' ' . $bundle . ' content';
            $this->roleHasPermission($role, $permission);
            $allowed_roles[] = $role;
        }
        $allowed_roles[] = 'administrator';

        $all_roles = $this->getRoles();
        foreach ($all_roles as $role)
        {
            if (!in_array($role, $allowed_roles))
            {
                $this->roleDoesNotHavePermission($role, $permission);
            }
        }
    }

    /**
     * Helper Functions Only below.
     */

    /**
     * Get most recent node id.
     *
     */
    public static function getLastCreatedNode()
    {
        $query = \Drupal::database()->select('node_field_data', 'nfd');
        $query->addField('nfd', 'nid');
        $query->range(0, 1);
        $query->orderBy("nid", 'DESC');
        $nid = $query->execute()
            ->fetchField();

        return $nid;
    }

    /**
     * Get most recent media id.
     *
     */
    public static function getLastCreatedMedia()
    {
        $query = \Drupal::database()->select('media_field_data', 'mfd');
        $query->addField('mfd', 'mid');
        $query->range(0, 1);
        $query->orderBy("mid", 'DESC');
        $mid = $query->execute()
            ->fetchField();

        return $mid;
    }

    /**
     * Users with the $role should have the $permission.
     */
    public function roleHasPermission($role, $permission)
    {
        $roleObj = Role::load($role);
        Assert::assertNotEmpty($roleObj->hasPermission($permission) , $role . ' role does not have permission to ' . $permission);
    }

    /**
     * Users with the $role should not have the $permission.
     */
    public function roleDoesNotHavePermission($role, $permission)
    {
        $roleObj = Role::load($role);
        Assert::assertEmpty($roleObj->hasPermission($permission) , $role . ' role has permission to ' . $permission . ', but should not.');
    }

    /**
     * Get all user roles.
     */
    public function getRoles()
    {
        $roles = user_roles();
        $roles = array_keys($roles);
        return $roles;
    }

    /**
     * This works for Selenium and other real browsers that support screenshots.
     *
     * @Then /^save a screenshot$/
     */
    public function save_a_screenshot()
    {
        $image_data = $this->getSession()
            ->getDriver()
            ->getScreenshot();
        $file_and_path = '/tmp/behat_screenshot.jpg';
        file_put_contents($file_and_path, $image_data);

        if (PHP_OS === "Darwin" && PHP_SAPI === "cli")
        {
            exec('open -a "Preview.app" ' . $file_and_path);
        }
    }

    /**
     *
     * @Then /^scroll page to the bottom$/
     */
    public function scrollPageBottom()
    {
        $this->getSession()
            ->getDriver()
            ->executeScript('window.scrollTo(0,document.body.scrollHeight);');;
    }

    /**
     *
     * @Then /^scroll page to the top$/
     */
    public function scrollPageTop()
    {
        $this->getSession()
            ->getDriver()
            ->executeScript('window.scrollTo(0,0);');;
    }

    /**
     *
     * @Then /^zoom page out$/
     */
    public function zoomPageOut()
    {
        $this->getSession()
            ->getDriver()
            ->executeScript("document.body.style.zoom='50%';");
    }

    /**
     * Switches to the dialog
     * Example: Then I switch to the dialog
     * Example: Then I switch to the dialog
     *
     * @Then /^(?:|I )switch to the dialog$/
     */
    public function iSwitchToDialog()
    {
        $this->getSession()
            ->switchToIFrame();
    }

    /**
     * Fills in textarea field with specified name
     * Example: When I fill in "Question" textarea with "Question1"
     *
     * @When I fill in :arg1 textarea with :arg2
     *
     * @throws \Exception;
     */
    public function iFillTextArea($name, $text)
    {
        //$xpath = "//IFRAME[@id='{$name}' or @name='{$name}' or @title='{$name}']";
        //$this->getSession()->getDriver()->switchToIFrame();
        //print_r($this->getSession()->getDriver()->getWindowNames());
        //$fieldXpath = "//html//body";
        //$fieldXpath = "//iframe[contains(@title, 'Rich Text Editor, Answer field')]";
        //$fieldXpath = "//label[(text() = '" . $name . "')]/ancestor::div[contains(@class, 'js-form-type-textarea')]//div[contains(@class, 'form-textarea-wrapper')]//textarea";
        $fieldXpath = "//textarea[contains(@id, 'edit-field-qa-item-question')]";
        $this->getSession()
            ->getDriver()
            ->click($fieldXpath);
        $this->getSession()
            ->getDriver()
            ->keyPress($fieldXpath, "77");
        $this->getSession()
            ->getDriver()
            ->keyPress($fieldXpath, "77");
        $this->getSession()
            ->getDriver()
            ->keyPress($fieldXpath, "77");

        $fieldXpath = "field_qa_item_question[0][value]";
        $this->getSession()
            ->getPage()
            ->fillField($fieldXpath, $text);
        $this->getSession()
            ->getDriver()
            ->setValue($fieldXpath, $text);
    }
}

