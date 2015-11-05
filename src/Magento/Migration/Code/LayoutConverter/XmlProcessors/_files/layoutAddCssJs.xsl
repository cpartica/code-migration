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

    <!-- Update action node for css & append module prefix -->
    <xsl:template match="*[name()='action' and @method='addJs']">
        <xsl:element name="js">
            <xsl:attribute name="class">
                <xsl:value-of select="concat($moduleName, '::', node()[text()])"/>
            </xsl:attribute>
        </xsl:element>
    </xsl:template>

    <!-- Update action node for css & append module prefix -->
    <xsl:template match="*[name()='action' and @method='addCss']">
        <xsl:element name="css">
            <xsl:attribute name="src">
                <xsl:value-of select="concat($moduleName, '::', node()[text()])"/>
            </xsl:attribute>
        </xsl:element>
    </xsl:template>

    <!-- Update action setTemplate & append module prefix -->
    <xsl:template match="*[name()='action' and @method='setTemplate']/template">
        <xsl:element name="template">
                <xsl:value-of select="concat($moduleName, '::', text())"/>
        </xsl:element>
    </xsl:template>

</xsl:stylesheet>
