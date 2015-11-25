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

    <!-- convert router args -->
    <xsl:template match="/config/router/route">
        <xsl:choose>
            <xsl:when test="args/modules/*">
                <xsl:element name="route">
                    <xsl:attribute name="id">
                        <xsl:value-of select="@name"/>
                    </xsl:attribute>
                    <xsl:for-each select="args/modules/*">
                        <xsl:element name="module">
                            <xsl:attribute name="name">
                                <xsl:value-of select="name()"/>
                            </xsl:attribute>
                            <xsl:apply-templates select="@*"/>
                        </xsl:element>
                    </xsl:for-each>
                </xsl:element>
            </xsl:when>
            <xsl:otherwise>
                <xsl:element name="route">
                    <xsl:attribute name="id">
                        <xsl:value-of select="@name"/>
                    </xsl:attribute>
                    <xsl:attribute name="frontName">
                        <xsl:value-of select="args/frontName"/>
                    </xsl:attribute>
                </xsl:element>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
