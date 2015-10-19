<!--
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    extension-element-prefixes="php"
    exclude-result-prefixes="xsl php">

    <xsl:output method="xml" omit-xml-declaration="yes"/>
    <xsl:variable name="schemaPath" select="'urn:magento:framework:View/Layout/etc/page_configuration.xsd'"/>

    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>

    <!-- Transfer handle labels for blocks or containers into attributes -->
    <xsl:template match="/page//block[./label]|page//container[./label]">
        <xsl:element name="{name(.)}">
            <xsl:attribute name="label">
                <xsl:value-of select="./label"/>
            </xsl:attribute>
            <xsl:apply-templates select="*[name()!='label']|@*[name()!='translate']"/>
        </xsl:element>
    </xsl:template>

    <!-- remove all labels under body, they moved in etc/page_types -->
    <xsl:template match="/page/body/label">
    </xsl:template>

</xsl:stylesheet>
