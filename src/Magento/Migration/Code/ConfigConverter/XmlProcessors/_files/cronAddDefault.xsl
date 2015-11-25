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

    <!-- convert observers -->
    <xsl:template match="/config">
        <xsl:element name="{name()}">
            <xsl:apply-templates select="/config[name='config']|@*"/>
            <xsl:element name="group">
                <xsl:attribute name="id">
                    <xsl:value-of select="'default'"/>
                </xsl:attribute>
                <xsl:apply-templates select="node()|@*[name()!='xsi:noNamespaceSchemaLocation']"/>
            </xsl:element>
        </xsl:element>
    </xsl:template>
</xsl:stylesheet>
