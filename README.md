CODE MIGRATION TOOLKIT
 
The Magento Code Migration Toolkit provides scripts that eases the process of converting your custom Magento 1.x module to Magento 2.0 by handling some of the most time-consuming conversion tasks. Specifically, you can run the scripts that comprise this toolkit to automatically convert:

* module directory structure (M1 and M2 module directory structures differ in key ways.) 

* PHP files

* config.xml  files

* layout.xml files


ABOUT THIS TOOLKIT

This toolkit can significantly reduce the work involved in migrating your M1 module to M2. However, after running this toolkit to migrate your Magento 1.x installation, you will need to manually edit some files in your installation. You must run the conversion scripts in the specified order as the output from one script may be used as input for another script. 


PREREQUISITES

* PHP  5.5.x or greater

* Composer package management software.  This package management software installs the Magento 2.x Framework, a requirement for installing and running the code migration toolkit.  See https://getcomposer.org/download/ for more information about Composer. 

* Designated source (Magento 1.x) and target (Magento 2.0) directories. The modules you’ve designated for conversion must reside in your designated source directory. 

You do not need to install a web server to run the code migration toolkit. 

INSTALLATION
Install Composer in your migration toolkit root directory.  This installs the toolkit’s dependency packages,  including the Magento 2.x Framework, which forms the basis of this toolkit.


OVERVIEW OF MODULE MIGRATION PROCESS


Step one: Migrate Magento 1.x module structure to Magento 2.0 structure (bin/migrate.php migrateModuleStructure).

Step two: Migrate Magento 1.x  layout.xml Magento 2.0 structure file structure. (bin/migrate.php convertLayout).

Step three: Migrate PHP code (bin/migrate.php convertPhpCode). 

The code migration toolkit deletes your Magento 1.x layout files (mymodulelayout.xml)  after processing, and places the converted files in the same view folder.
Converted files are not overwritten. All converted code is saved with a *.converted extension in the same folder as the original file.


RUNNING THE TOOL

This toolkit provides three scripts that perform distinct migration tasks and which must be run in the following order. 

* bin/migrate.php migrateModuleStructure

* bin/migrate.php convertLayout

* bin/migrate.php convertPhpCode



CONVERTING A MODULE

You can specify a Magento 1.x root folder installation with the already installed modules, or just one module. If you designate a folder with multiple modules, all modules must follow Magento 1.x naming and structure conventions.  

Each step is required to be made in the order presented to ensure a successful module conversion


STEP ONE:  Migrate Magento 1.x module structure to Magento 2.0 structure

This script does not convert code. It simply creates the appropriate Magento 2.0 module directory structure and moves the existing Magento 1.x code there. The output of this script is used as input by other scripts in the toolkit. 

Prerequisite: Confirm all source files follow Magento 1.x naming and structure conventions. app/code/local/vendor/module/[module_files] app/design//[module_files] media/[module_files] skin//[module_files]. 


Run bin/migrate.php migrateModuleStructure. This command converts one or more modules from the Magento 1.x structure to Magento 2. Note that in Magento 2.0, all module code resides in its own directory. This script identifies each module and copies its files to the Magento 2.x structure (/app/code/vendor/module/[module_files]). 

For more information on the Magento 2.0 module structure, see the Magento on-line documentation set. 



STEP TWO: Convert layout files from M1 to M2
This script splits Magento 1.x layout files and converts them to Magento 2.x xml handlers in app/code/vendor/module/view//layout/.xml

Run bin/migrate.php convertLayout. 

Prerequisite: Run script in Step One.

The convertLayout script converts the contents of the handlers using the mapping files. 
The script  automatically tries to map and include/merge mapping found in the input modules. Pleasef you feel that some mapping weren't picked up, then you might need to regenerate the mapping files from a magento installation that already has installed the modules that you are trying to convert


This convertLayout tool does the following conversion-related tasks:

* Splits the Magento 1.x layout.xml into multiple files. Each new layout file contains a handler.

* Removes the tag and adds the and tags with the proper schemas.

* Transforms the block type attribute to class and converts the name to Magento 2.x class. 

* Converts to where needed removing the class on the container.

* Converts to referenceBlock or referenceContainer.

* Adds arguments instead of tag names for parameters on actions.

* Converts the translation labels as attributes.




STEP THREE: Convert PHP code found in blocks, controllers, models, helpers

This script converts most of the PHP code in the target directory to the Magento 2.x requirements.

Prerequisite: Although you can run this script without first completing Steps one and two, we recommended first running Steps one and two for optimal accuracy.

Run bin/migrate.php convertPhpCode. The input path can be a file, a folder, a module or the entire output of the converted structure.

 The convertPhpCode does the following conversion-related tasks:

* Adds PHP 5.3+ namespaces used in Magento 2.x framework

* Converts class name and full qualifier name for extends and implements

* Identifies all Mage:: dependencies and modifies the constructor and injects the dependencies and creates properties that are assigned to the dependency classes

* Processes all Mage::getModel and Mage::getHelper and Mage::getSingleton and identifies the proper class factories

* Replaces all Mage::* with the proper call through the property that identifies that call

* Replaces all references to an Magento 1.x class with the name qualifier for the Magento 2.x class

* Handles Mage::throwException replacing it with the new namespace exceptions

* Replaces all requests with Mage::app() by injecting a dependency for the request or cache, etc.

* PSR logging with Mage::logException replaced by \Psr\Log\LoggerInterface Mage::registry and Mage::register, Mage::unregister replacement with dependency injection

* Replaces all getTable and related aliases with real table names

Extra information about the migration tool * bin/utils.php - generates mapping files intended to improve conversion used internally




UNDERSTANDING MAPPING FILES

We use mapping files to generate lists of classes as they were transformed from Magento 1.x to Magento 2.x  These mapping files capture these gerbil differences between Magento 1.x and Magento 2.0 files:

* files are renamed in case of names in XML

* obsolete classes

* classes named according to the new namespace conventions for PHP 5.4+.

Most of these mappings are generated with our utils automated tool but some that have the *_manual suffix are maintained manually because an automated process could not resolve those names, but subsequent processes are dependent on them.


Mappings for Magento 1.x names located in the mapping/*.json or mapping/*.xml. These mapping files provides information to the tool or developers that are used to automatically or manually port legacy Magento 1.x code


 Note: To provide more accurate mapping with the custom names defined in your own module, you must regenerate and merge these mappings.

MAPPING FILE

 * mapping/aliases.json - List of Magento 1.x CE module block aliases defined in /etc/config.xml to identify a Block class namespace as Magento 2.x will need the full class for blocks


 * mapping/aliases_ee.json - List of Magento 1.x EE module block aliases defined in /etc/config.xml to identify a Block class namespace as Magento 2.x will need the full class ufor blocks

 * mapping/class_dependency.json - List of Magento 1.x class dependencies used for automatic vs manual class mapping

 * mapping/class_dependency_aggregated.json - List of agregated Magento 1.x class dependencies used for automatic vs manual class mapping

 * mapping/class_mapping.json - List of automatically generated Magento 1.x (CE+EE) namespaces that map to Magento 2.x (CE+EE) namespaces for latest PHP convetions and proprietary framework

 * mapping/class_mapping_manual.json- List of manually maintained, Magento 1.x (CE+EE) namespaces that map to Magento 2.x (CE+EE) namespaces for latest PHP convetions and proprietary framework

 * mapping/module_mapping.json - List of the module names in Magento 1.x (CE+EE) that maps to the equivalent Magento 2.x (CE+EE) names

 * mapping/table_names_mapping.json - List of table names aliases in Magento 1.x that and the real MySQL table names as Magento 2.x will not use aliases for getTable()

 * mapping/unmapped_classes.json - List of classes that are not present in the class_mapping*.json and are not maintained in the manual list but used in the Magento Core modules

 * mapping/view_mapping_adminhtml.json - List of the layout adminhtml handlers names in Magento 1.x that map to the Magento 2.x names that now are on a separate file using these namings

 * mapping/view_mapping_frontend.json - List of the layout frontend handlers names in Magento 1.x that map to the Magento 2.x names that now are on a separate file using these namings

 * mapping/references.xml - List of references used in layouts that point to blocks and containers in Magento 2.x vs Magento 1.x that didn't had this concept, as all were blocks



REGENERATE THE MAPPING FILE (OPTIONAL)
Note:  As a best practice, we do not recommend regenerating mapping files. However, you might choose to  but we provide this tool if you add customizations to your Magento installation. If you include custom modules in your installation, the code migration toolkit maps artifacts found in those modules, which will result in a more comprehensive list. You can create one or more mapping files.

COMMAND SYNTAX

 bin/utils.php CommandParam Param1 [Param2]
 bin/migrate.php CommandParam Param1 [Param2]

PARAMETERS

 * bin/utils.php generateClassDependency <m1> - Requires a Magento 1.x installation. Affects  mapping/class_dependency.json and mapping/class_dependency_aggregated.json


 * bin/utils.php generateClassMapping <m1> <m2> - Requires a Magento 1.x and Magento 2.x installation. Affects mapping/class_mapping.json and mapping/unmapped_classes.json.


 * bin/utils.php generateModuleMapping <m1> <m2> - Requires a Magento 1.x and Magento 2.x installation. Affects module_mapping.json.

 * bin/utils.php generateTableNamesMapping <m1> - Requires a Magento 1.x installation. Affects table_names_mapping.json.

 * bin/utils.php generateViewMapping <m1> <m2> - Requires a Magento 1.x and Magento 2.x installation. Affects mapping/view_mapping_adminhtml.json and mapping/view_mapping_frontend.json, mapping/references.xml.

 * bin/migrate.php generateAliasMapping <m1> <m2> - Requires a Magento 1.x and Magento 2.x installation. Affects mapping/aliases.json.

 * bin/migrate.php generateAliasMappingEE <m1> <m2> - Requires a Magento 1.x and Magento 2.x installation. Affects mapping/aliases_ee.json.

