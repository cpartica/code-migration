[![Build Status](https://travis-ci.org/magento/code-migration.svg?branch=develop)](https://travis-ci.org/magento/code-migration)

Code Migration Toolkit
======================
 
The Magento Code Migration Toolkit provides scripts that ease the process of converting custom Magento 1.x code to Magento 2 by handling some of the most time-consuming conversion tasks. The toolkit is intended for Magento developers with reasonable expertise in both Magento 1.x and Magento 2.


## Scope

The toolkit covers migration of the following aspects of the Magento code:

* Module directory structure

* Layout XML files

* Config XML files

* PHP files

Migration of Magento modules is the focus of the toolkit. Magento themes are out of scope of the toolkit at the moment.

The toolkit can significantly reduce the work involved in the code migration. However, after running the toolkit one will need to manually edit some of the generated files.


## System Requirements

* PHP >= 5.5

* [Composer](https://getcomposer.org/) dependency management software

Note that being a command line tool the toolkit does not require a web server to be installed.


## Installation

Run `composer install` in the migration toolkit root directory. This installs the toolkit’s dependency packages, including the Magento 2.x Framework, which forms the basis of the toolkit.


## Prerequisite Directories

Before running the migration, the following directories need to be prepared:

* `<src>` - Directory that contains custom Magento 1.x code that is intended to be migrated. The code must follow the Magento 1.x directory structure. Magento 1.x core files must not be included.

* `<dst>` - Empty directory for the toolkit to put the generated Magento 2 code to

* `<m1>` - Directory that contains:

 * Vanilla Magento 1.x codebase, and

 * Custom Magento 1.x code same as in `<src>` directory, and

 * Dependencies of the custom Magento 1.x code, if any, that are not part of `<src>` directory as not intended to be migrated at the moment

* `<m2>` - Directory that contains the vanilla Magento 2.x codebase

The above directories are not required to be positioned relatively to each other, they can be anywhere in the file system.

Note that the Magento instances living in the directories `<m1>` and `<m2>` may not necessarily be installed. Being a static analysis tool, the toolkit does not execute the Magento source code.


## Migration Procedure

The migration consists of the following steps:

1. Migrate Magento 1.x module structure to the structure recognized in Magento 2

2. Migrate Magento 1.x layout XML files to the format recognized in Magento 2

3. Migrate Magento 1.x config XML files to the format recognized in Magento 2

4. Migrate the PHP code in terms of how it interacts with the Magento framework, preserving the business logic

Note that it may be necessary to regenerate the mapping files as the very first step. That is especially important in a case of migration between arbitrary Magento 1.x and Magento 2.x versions rather than the latest ones. It is an optional step, but it greatly influences the quality of the migration results. Please refer to the mapping files discussion later in the document.


## Running the Migration

Run all the migration scripts:

1. `php bin/migrate.php migrateModuleStructure <src> <dst>` - Migrate directory structure

2. `php bin/migrate.php convertLayout <dst>` - Migrate layout

3. `php bin/migrate.php convertConfig <dst>` - Migrate config

4. `php bin/migrate.php convertPhpCode <dst> <m1> <m2>` - Migrate PHP code

The migration scripts are to be ran in the specified order as the output from one script may be used as input for another script.

The scripts read the Magento 1.x code from the `<src>` directory, convert it, and write the transformed code to the `<dst>` directory. All converted code is saved to files with a `*.converted` extension. The directories `<m1>` and `<m2>` are used to lookup the context of the code being migrated.


## Understanding Migration Scripts

The toolkit comes with the flexibility to adjust certain migration steps by configuring or skipping execution of respective migration scripts. This section discusses specifics of each of the migration scripts.

### Module Structure Migration Script

This script does not alter contents of any files. Instead, it simply creates the appropriate Magento 2 module directory structure and moves the existing Magento 1.x code there. The output of this script is used as input by other scripts in the toolkit.

### Layout Migration Script

This script splits Magento 1.x layout files into separate XML files for each layout handle and converts their XML format to the one used in Magento 2.

The script converts the contents of the handles using the mapping files. The script automatically tries to map and include/merge mapping found in the input modules. If you feel that some mapping were not picked up, then you might need to regenerate the mapping files from a magento installation that already has installed the modules that you are trying to convert.

The script does the following conversion-related tasks:

* Splits the Magento 1.x layout files into separate XML files for each layout handle

* Replaces the root tag and adds the proper schemas

* Transforms the block type attribute to class and converts the name to Magento 2.x class

* Converts to where needed removing the class on the container

* Converts `<reference>` to `<referenceBlock>` or `<referenceContainer>`

* Converts format of action arguments

* Converts the translation labels as attributes

### Config Migration Script

This script splits Magento 1.x config XML files and converts them to Magento 2.x XML format.

### PHP Code Migration Script

This script converts most of the PHP code to be compatible with the Magento 2 Framework.

The script does the following conversion-related tasks:

* Adds PHP 5.3+ namespaces used in Magento 2.x framework

* Converts class name and full qualifier name for extends and implements

* Identifies all dependencies on the `Mage` class and modifies the constructor and injects the dependencies and creates properties that are assigned to the dependency classes

* Processes all `Mage::getModel`, `Mage::getHelper`, `Mage::getSingleton` and identifies the proper class factories

* Replaces all references to an Magento 1.x class with the name qualifier for the Magento 2.x class

* Handles `Mage::throwException` replacing it with the new namespace exceptions

* Replaces all occurrences of `Mage::app` by injecting a dependency for the request, cache, etc.

* PSR logging with `Mage::log` and `Mage::logException` replaced by `\Psr\Log\LoggerInterface`

* Registry manipulation with `Mage::registry` and `Mage::register`, `Mage::unregister` replaced by dependency injection

* Replaces all `getTable` and related table aliases with real table names


## Understanding Mapping Files

Mapping files are used to generate lists of classes as they were transformed from Magento 1.x to Magento 2.x. These mapping files capture these gerbil differences between Magento 1.x and Magento 2.0 files:

* Files are renamed in case of names in XML

* Obsolete classes

* Classes named according to the new namespace conventions for PHP 5.3+

Most of these mappings are generated in automated fashion but some that have the `*_manual` suffix are maintained manually because an automated process could not resolve those names, but subsequent processes are dependent on them.

Mappings for Magento 1.x names located in the `mapping/*.json` or `mapping/*.xml`. These mapping files provides information to the tool or developers that are used to automatically or manually port legacy Magento 1.x code.

### Mapping File Reference

The list of mapping files that come with the toolkit:

* `mapping/aliases.json` - List of Magento 1.x CE module aliases defined in modules' config files to identify class namespaces as Magento 2.x will need the full class

* `mapping/aliases_ee.json` - List of Magento 1.x EE module block aliases defined in modules' config files to identify class namespaces as Magento 2.x will need the full class

* `mapping/class_dependency.json` - List of Magento 1.x class dependencies used for automatic vs manual class mapping

* `mapping/class_dependency_aggregated.json` - List of aggregated Magento 1.x class dependencies used for automatic vs manual class mapping

* `mapping/class_mapping.json` - List of automatically generated Magento 1.x (CE+EE) namespaces that map to Magento 2.x (CE+EE) namespaces for latest PHP conventions and proprietary framework

* `mapping/class_mapping_manual.json` - List of manually maintained, Magento 1.x (CE+EE) namespaces that map to Magento 2.x (CE+EE) namespaces for latest PHP conventions and proprietary framework

* `mapping/module_mapping.json` - List of the module names in Magento 1.x (CE+EE) that maps to the equivalent Magento 2.x (CE+EE) names

* `mapping/table_names_mapping.json` - List of table name aliases in Magento 1.x that map to the real MySQL table names as Magento 2.x will not use aliases for `getTable`

* `mapping/unmapped_classes.json` - List of classes that are not present in the `mapping/class_mapping*.json` and are not maintained in the manual list but used in the Magento core modules

* `mapping/view_mapping_adminhtml.json` - List of the layout `adminhtml` handle names in Magento 1.x that map to the Magento 2.x names that now are on a separate file using these namings

* `mapping/view_mapping_frontend.json` - List of the layout `frontend` handle names in Magento 1.x that map to the Magento 2.x names that now are on a separate file using these namings

* `mapping/references.xml` - List of references used in layouts that point to blocks and containers in Magento 2.x vs Magento 1.x that did not have this concept, as all were blocks

### Regenerate Mapping Files

Quality of the migration greatly relies on the accuracy of mapping files. The toolkit comes with mapping files pre-generated for the latest Magento 1.x and Magento 2 releases out of the box. Migration between any given Magento 1.x and Magento 2 versions would require mappings to be regenerated prior to running the migration scripts.

Run the following to regenerate the mapping files:

* `php bin/utils.php generateClassDependency <m1>` - Regenerate `mapping/class_dependency.json` and `mapping/class_dependency_aggregated.json`

* `php bin/utils.php generateClassMapping <m1> <m2>` - Regenerate `mapping/class_mapping.json` and `mapping/unmapped_classes.json`

* `php bin/utils.php generateModuleMapping <m1> <m2>` - Regenerate `mapping/module_mapping.json`

* `php bin/utils.php generateTableNamesMapping <m1>` - Regenerate `mapping/table_names_mapping.json`

* `php bin/utils.php generateViewMapping <m1> <m2>` - Regenerate `mapping/view_mapping_adminhtml.json` and `mapping/view_mapping_frontend.json`, `mapping/references.xml`

* `php bin/migrate.php generateAliasMapping <m1>` - Regenerate `mapping/aliases.json`

* `php bin/migrate.php generateAliasMappingEE <m1>` - Regenerate `mapping/aliases_ee.json`
