# This repository contains code migration tools that can be used to help migrating extentions for Magento 1 to Magento 2. 

This tool is intended to convert Magento 1.x custom modules to Magento 2.x by converting the module structure, PHP code, xml in config files and layout files
The purpose of this tool is to produce the most comprehensive migration, as close as possible usage but does not guarantee 100% success
It's intended to lower the conversion time to port a module from Magento 1.x to Magento 2.x

It is composed from multiple command line scripts with different parameters each one having a role into this process

The command line is located in the "bin" folder:
  * bin/migrate.php - converts structure, code, xml
  
  Perquisites:
  
   Terminal or command line for mac/unix/linux or windows
   PHP > 5.5.x cli (there is no web server needed to run)
   Composer To install magento migration tool we need composer. See https://getcomposer.org/download/ for details
  
  Installation
  
   Run composer install from your migration tool root directory
   This installs dependency packages including the Magento 2.x framework that this tool is based on
   php version
   
  About the tool
  
  In order to run the conversion tools you need a Magento 1.x installation folder and a Magento 2.x installation folder
  You also need the input module(s) that you are trying to convert
  

  Steps for converting a module:
  
  Using some of command line you can migrate a module from Magento 1.x to Magento 2.x using a suite of multiple commands that do different levels of conversion
    
  Steps to follow:
    
  The available commands are:

    * bin/migrate.php migrateModuleStructure
    * bin/migrate.php convertLayout
    * bin/migrate.php convertPhpCode
    
  Each step is required to be made in the order presented to ensure a successful module conversion
    
  1. Structure conversion from Magento 1.x to Magento 2.x
   * bin/migrate.php migrateModuleStructure <m1> <m2>
   
    Converts one or more modules from Magento 1.x structure to Magento 2.x. Note that in Magento 2.x now modules are self-contained into the same 
    You can specify a whole Magento 1.x root folder installation with the already installed modules, or just one module that as long it has the same structure
    No code conversion is done at this point
    
    Parameter(s) specification
    <m1> parameter has to be a folder with the following Magento 1.x stucture:
    
    app/code/local/vendor/module/[module_files]
    app/design/*/[module_files]
    media/[module_files]
    skin/*/[module_files]
  
    The tool will identify each module and it will copy all it's files to the Magento 2.x structure
    
    /app/code/vendor/module/[module_files] - each folder containing it's files
    
    Note: We will use this as an input to some of the next steps
   
   Where m1 is a Magento 1.x
   
  2. Converting layout files 
   * bin/migrate.php convertLayout <inputPath>
   Splits Magento 1.x layout files and converts them to Magento 2.x xml handlers in app/code/vendor/module/view/*/layout/*.xml
   
   Parameter(s) specification
   <inputPath> is the already converted on step 1 module. make sure you use an input a path that has the Magento 2.x structure as output from the step 1
   
   eg:
   app/code/vendor/module/view/frontend/layout/mymodulelayout.xml
   
   that contains all handlers into one file, and a different structure than Magento 2.x versions
   
   will result to:
   app/code/vendor/module/view/frontend/layout/layout_handler_1.xml
   app/code/vendor/module/view/frontend/layout/layout_handler_2.xml
   app/code/vendor/module/view/frontend/layout/layout_handler_3.xml
   
   Note: old Magento 1.x layout files (eg: mymodulelayout.xml) will be deleted after processing and files will be placed in the same view folder
   
   The contents of the handlers are converted too using some automatic steps of rules that use the mapping files
   
   This step will automatically try to map and include/merge mapping found in the input modules
   If you feel that some mapping weren't picked up, then you might need to generate again the mapping files from a magento installation that already has installed the modules that you are trying to convert
   
   The layout conversion has the following supported features:
    - splits the layout into multiple files each one containing a handler
    - removes the <layout> tag and adds the <page> and <body> tags with the proper schemas
    - transforms the block type attribute to class and converts the name to Magento 2.x class
    - converts <block> to <container> where needed removing the class on the container
    - converts <reference> to referenceBlock or referenceContainer
    - adds arguments instead of tag names for parameters on actions
    - converts the translation labels as attributes
   
   3. Converting PHP code found in blocks, controllers, models, helpers etc.
   * bin/migrate.php convertPhpCode <path> [<m1BaseDir>] [<m2BaseDir>]
   
   Converts most of the php code to the Magento 2.x requirements, a full list of supported features is provided below:
   
   Parameter(s) specification
   <path> - the input path can be a file, a folder, a module or the entire output of the converted structure. you can also use this step wihtout converting the structure
   [<m1BaseDir>] - the Magento 1.x installation folder - this is optional but recommended for accuracy
   [<m2BaseDir>] - the Magento 2.x installation folder - this is optional but recommended for accuracy
   
   
   Supported features:
    - Adds PHP 5.3 and later namespaces used in Magento 2.x framework
    - Converts class name and full qualifier name for extends and implements
    - Identifies all Mage:: dependencies and modifies the constructor and injects the dependencies and creates properties that are assigned to the dependency classes
    - Processes all Mage::getModel and Mage::getHelper and Mage::getSingleton and identifies the propper class even factories 
    - Replaces all Mage::* with the propper call through the property that identifies that call
    - Replaces all references to an Magento 1.x class with the name qualifier for the Magento 2.x class
    - Handles Mage::throwException replacing it with the new namespace exceptions
    - Replaces all requests with Mage::app() by injecting a dependency for the request or cache, etc
    - PSR logging with Mage::logException replaced by \Psr\Log\LoggerInterface
    - Mage::registry and Mage::register, Mage::unregister replacement with dependency injection
    - Replaces all getTable and related aliases with real table names
    
   Note: Converted files are not overwritten, all converted code is saved with a *.converted extension in the same folder as the original file
   
   
   Extra information about the migration tool
     * bin/utils.php - generates mapping files intended to improve conversion used internally
     
   Understanding mapping files:
     The reason of why we use mapping files is to provide lists of classes or other namings as they were transformed from Magento 1.x to Magento 2.x
   Some of them were just renamed in case of names in XML, some are not used anymore as they were deprecated becoming obsolete, or in case of classes they use the new namespacing convention for PHP > 5.4
     
     This tool provides mappings for Magento 1.x namings located in the mapping/*.json or mapping/*.xml. This provides information to the toold or developers to port automatically or manually old Magento 1.x code
     Note: In order to provide more accurate mapping with the custom namings defined in your own module, these mappings have to be re-generated and merged to obtain an accurate list of both Magento 1.x code and your code
           Most of these mappings are generated with our utils automated tool but some that have the *_manual suffix are maintained manually because an automated process could not resolve those namings but later processes are dependent on them
     
     Mapping files list:
     
     * mapping/aliases.json - List of Magento 1.x CE module block aliases defined in /etc/config.xml to identify a Block class namespace as Magento 2.x will need the full class ufor blocks
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
    
     Regenerating mapping files is not recommended, but this tool is provided if you have some custom modifications to your Magento installation 
     
     Command line map re-generating:
     Using bin/utils.php and  bin/migrate.php, you can generate the mapping files usually having as an input an Magento 1.x intallation. If custom modules are included into that installation we will map the artifacts found in those modules too resulting in a more complete list
     One or more mapping files are generated using the proper main command parameter and additional parameters for input
     
     bin/utils.php CommandParam Param1 [Param2]
     bin/migrate.php CommandParam Param1 [Param2]
         
     * bin/utils.php generateClassDependency <m1> - Requires a Magento 1.x installation, affects  mapping/class_dependency.json and mapping/class_dependency_aggregated.json
     * bin/utils.php generateClassMapping <m1> <m2> - Requires a Magento 1.x and Magento 2.x installation, affects mapping/class_mapping.json and mapping/unmapped_classes.json
     * bin/utils.php generateModuleMapping <m1> <m2> - Requires a Magento 1.x and Magento 2.x installation, affects module_mapping.json
     * bin/utils.php generateTableNamesMapping <m1> - Requires a Magento 1.x installation, affects table_names_mapping.json
     * bin/utils.php generateViewMapping <m1> <m2> - Requires a Magento 1.x and Magento 2.x installation, affects mapping/view_mapping_adminhtml.json and mapping/view_mapping_frontend.json, mapping/references.xml
     * bin/migrate.php generateAliasMapping <m1> <m2> - Requires a Magento 1.x and Magento 2.x installation, affects mapping/aliases.json
     * bin/migrate.php generateAliasMappingEE <m1> <m2> - Requires a Magento 1.x and Magento 2.x installation, affects mapping/aliases_ee.json
