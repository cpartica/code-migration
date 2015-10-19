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

    <xsl:output method="xml" omit-xml-declaration="yes" indent="yes"/>

    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>


    <xsl:template match="layout/@version"></xsl:template>

    <!-- Replace layout (document root) node -->
    <xsl:template match="layout">
            <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
                <xsl:apply-templates select="@*|node()"/>
            </page>
    </xsl:template>


    <!-- Replace handle node with the body tag-->
    <xsl:template match="*[name(..)='layout']">
        <body>
            <xsl:apply-templates select="@*|node()"/>
        </body>
    </xsl:template>

    <!-- Update block node attribute class for type -->
    <xsl:template match="block[@type]">
        <xsl:copy>
            <xsl:attribute name="class">
                <xsl:value-of select="attribute::type" />
            </xsl:attribute>
            <xsl:apply-templates select="node()|@*[name()!='type']"/>
        </xsl:copy>
    </xsl:template>

</xsl:stylesheet>
