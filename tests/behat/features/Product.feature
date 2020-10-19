Feature: Product Test
  @javascript
  Scenario: Product Test
    When I login into Drupal

#    # Add icon
#    And I am on "media/add/image"
#    And I attach the file "icon.png" to "Add a new file"
#    And I wait for the ajax response
#    TODO: Wait for picture to be loaded properly after it's added to the field
#    And I fill in "Name" with "icon"
#    And I press "Save"
#    Then save a screenshot
#    Then the url should match "admin/content/media"
#    And I should see "Image icon has been created."

#    # Delete icon
#    When I am on "admin/content/media"
#    And I click on "//button[contains(@class, 'dropbutton__toggle')]" xpath element
#    And I click on "//li[contains(@class, 'delete')]/a" xpath element
#    Then I should see "Are you sure you want to delete the media item icon? "
#    When I press "Delete"
#    Then I should see "The media item icon has been deleted."


    # Add format
    And I am on "admin/structure/taxonomy/manage/mars_format/add"
    And I fill in "Name" with "Format1"
    And I expand "Relations" area
    And I fill in "Weight" with "5"
    And I press "Save"
    Then the url should match "admin/structure/taxonomy/manage/mars_format/add"
    And I should see "Status message"
    And I should see "Created new term Format1."

    # Add flavor
    When I am on "admin/structure/taxonomy/manage/mars_flavor/add"
    And I fill in "Name" with "Flavor1"
    And I expand "Relations" area
    And I fill in "Weight" with "5"
    And I press "Save"
    Then the url should match "admin/structure/taxonomy/manage/mars_flavor/add"
    And I should see "Status message"
    And I should see "Created new term Flavor1."

    # Create product
    And I follow "Content"
    Then I should see "Add content"
    When I follow "Add content"
    Then I should see "Product"
    When I follow "Product"
    Then I should see "Create Product"

    When I fill in "Title" with "My Title"
    And I select "Flavor1" from "Flavor"
    When I fill in "Market" with "My Market"
    When I fill in "Sub Brand" with "My Sub Brand"
    When I fill in "Segment" with "My Segment"
    When I fill in "Product Name" with "Product Name"
    And I select "Format1" from "Format"
    And I click on "//a[contains(text(), 'Variants')]" xpath element

    # Delete format
    When I am on "admin/structure/taxonomy/manage/mars_format/overview"
    And I click link which contains "edit?destination=/admin/structure/taxonomy/manage/mars_format/overview"
    Then I should see "Edit term"
    When I follow "edit-delete"
    Then I should see "Are you sure you want to delete the taxonomy term Format1?"
    When I press "Delete"
    Then I should see "Deleted term Format1."

    # Delete flavor
    When I am on "admin/structure/taxonomy/manage/mars_flavor/overview"
    And I click link which contains "edit?destination=/admin/structure/taxonomy/manage/mars_flavor/overview"
    Then I should see "Edit term"
    When I follow "edit-delete"
    Then I should see "Are you sure you want to delete the taxonomy term Flavor1?"
    When I press "Delete"
    Then I should see "Deleted term Flavor1."
    And I close browser
