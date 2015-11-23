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
    <xsl:param name="schema"/>

    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>

    <!-- if config root exists just add atrributes -->
    <xsl:template match="/*[self::config]">
        <xsl:element name="config">
            <xsl:attribute name="xsi:noNamespaceSchemaLocation">
                <xsl:value-of select="$schema"/>
            </xsl:attribute>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:element>
    </xsl:template>

    <!-- add config root tag -->
    <xsl:template match="/*[not(self::config)]">
        <xsl:element name="config">
            <xsl:attribute name="xsi:noNamespaceSchemaLocation">
                <xsl:value-of select="$schema"/>
            </xsl:attribute>
            <xsl:copy>
                <xsl:apply-templates select="node()|@*"/>
            </xsl:copy>
        </xsl:element>
    </xsl:template>
</xsl:stylesheet>
