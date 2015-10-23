<!--
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:php="http://php.net/xsl"
    extension-element-prefixes="php"
    exclude-result-prefixes="xsl php">

    <xsl:output method="xml" omit-xml-declaration="yes"/>

    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>

    <!-- replace blocks with containers that contain some classes -->
    <xsl:template match="block[@class='core/text_list' or @class='page/html_wrapper' or @class='Mage_Core_Block_Text_List' or @class='Mage_Page_Block_Html_Wrapper']">
        <xsl:element name="container">
            <xsl:apply-templates select="node()|@*[name()!='core/text_list' and name()!='page/html_wrapper' and name()!='Mage_Core_Block_Text_List' and name()!='Mage_Page_Block_Html_Wrapper']"/>
        </xsl:element>
    </xsl:template>

</xsl:stylesheet>
