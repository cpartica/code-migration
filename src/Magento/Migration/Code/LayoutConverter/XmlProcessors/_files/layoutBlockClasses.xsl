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
    <xsl:variable name="classesStringXMlFile" select="'../../../../../../../mapping/class_mapping_manual.xml'"/>
    <xsl:variable name="classesDoc" select="document($classesStringXMlFile)"/>


    <xsl:template name="string-replace-all">
        <xsl:param name="text" />
        <xsl:param name="replace" />
        <xsl:param name="by" />
        <xsl:choose>
            <xsl:when test="contains($text, $replace)">
                <xsl:value-of select="substring-before($text,$replace)" />
                <xsl:value-of select="$by" />
                <xsl:call-template name="string-replace-all">
                    <xsl:with-param name="text" select="substring-after($text,$replace)" />
                    <xsl:with-param name="replace" select="$replace" />
                    <xsl:with-param name="by" select="$by" />
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$text" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>


    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>

    <!-- replace all block alias with the M1 class -->
    <xsl:template match="block[@class]">
        <xsl:copy>
            <xsl:variable name="aliasName" select="node()|@*[name()='class']"/>
            <xsl:variable name="aliasNode" select="$classesDoc/json/*[name()=$aliasName]/m2class"/>
            <xsl:choose>
                <xsl:when test="string-length($aliasNode)>0">
                                <xsl:attribute name="class">
                                    <xsl:value-of select="substring($aliasNode, 2, string-length($aliasNode))"/>
                                </xsl:attribute>
                                <xsl:apply-templates select="node()|@*[name()!='class']"/>
                    </xsl:when>
                <xsl:otherwise>

                    <!-- replace _ with \ to resemble M2 namespaces -->
                    <xsl:variable name="myVariable">
                        <xsl:call-template name="string-replace-all">
                            <xsl:with-param name="text" select="node()|@*[name()='class']" />
                            <xsl:with-param name="replace" select="'_'" />
                            <xsl:with-param name="by" select="'\'" />
                        </xsl:call-template>
                    </xsl:variable>
                    <xsl:attribute name="class">
                        <xsl:value-of select="$myVariable"/>
                    </xsl:attribute>
                    <xsl:apply-templates select="node()|@*[name()!='class']"/>

                </xsl:otherwise>
            </xsl:choose>
        </xsl:copy>
    </xsl:template>

</xsl:stylesheet>
