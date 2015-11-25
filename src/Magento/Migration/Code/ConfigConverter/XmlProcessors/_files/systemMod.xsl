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
    <xsl:param name="moduleName"/>
    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>


    <!-- transform groups -->
    <xsl:template match="//groups/*">
        <xsl:element name="group">
            <xsl:attribute name="id">
                <xsl:value-of select="name()"/>
            </xsl:attribute>
            <xsl:attribute name="type">
                <xsl:value-of select="frontend_type"/>
            </xsl:attribute>
            <xsl:attribute name="sortOrder">
                <xsl:value-of select="sort_order"/>
            </xsl:attribute>
            <xsl:attribute name="showInDefault">
                <xsl:value-of select="show_in_default"/>
            </xsl:attribute>
            <xsl:attribute name="showInWebsite">
                <xsl:value-of select="show_in_website"/>
            </xsl:attribute>
            <xsl:attribute name="showInStore">
                <xsl:value-of select="show_in_store"/>
            </xsl:attribute>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:element>
    </xsl:template>


    <!-- remove show_in_store tag -->
    <xsl:template match="//show_in_store">
    </xsl:template>

    <!-- remove show_in_default tag -->
    <xsl:template match="//show_in_default">
    </xsl:template>

    <!-- remove show_in_website tag -->
    <xsl:template match="//show_in_website">
    </xsl:template>

    <!-- remove sort_order tag -->
    <xsl:template match="//sort_order">
    </xsl:template>

    <!-- remove frontend_type tag -->
    <xsl:template match="//frontend_type">
    </xsl:template>


    <!-- transform fields in groups -->
    <xsl:template match="//fields/*">
        <xsl:element name="field">
            <xsl:attribute name="id">
                <xsl:value-of select="name()"/>
            </xsl:attribute>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:element>
    </xsl:template>

    <!-- remove groups tag -->
    <xsl:template match="//groups">
        <xsl:apply-templates select="node()|@*"/>
    </xsl:template>

    <!-- remove fields tag -->
    <xsl:template match="//fields">
        <xsl:apply-templates select="node()|@*"/>
    </xsl:template>

    <xsl:template match="/config/section">
        <xsl:element name="{name()}">
            <xsl:attribute name="id">
                <xsl:value-of select="@name"/>
            </xsl:attribute>
            <xsl:apply-templates select="node()|@*[name()!='name']"/>
        </xsl:element>
    </xsl:template>
</xsl:stylesheet>
