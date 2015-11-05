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
    <xsl:param name="moduleName"/>

    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="block[@template]">
        <xsl:copy>
            <xsl:attribute name="template">
                <xsl:value-of select="concat($moduleName, '::', @template)"/>
            </xsl:attribute>
            <xsl:apply-templates select="node()|@*[name()!='template']"/>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="argument[@name='template']/text()">
        <xsl:value-of select="concat($moduleName, '::', parent::node()/text())"/>
    </xsl:template>

</xsl:stylesheet>
