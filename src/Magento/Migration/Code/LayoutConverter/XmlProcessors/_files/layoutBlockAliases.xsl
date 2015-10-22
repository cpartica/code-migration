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
    <xsl:variable name="aliasesStringXMlFile" select="'../../../../../../../mapping/aliases.xml'"/>
    <xsl:variable name="aliasesDoc" select="document($aliasesStringXMlFile)"/>



    <!-- templates -->
    <xsl:variable name="upper" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
    <xsl:variable name="lower" select="'abcdefghijklmnopqrstuvwxyz'"/>
    <xsl:variable name="digit" select="'0123456789'"/>
    <xsl:variable name="alnum" select="concat(concat($upper, $lower), $digit)"/>


    <xsl:template name="cap-words">
        <!-- Capitalize the first letter of each word as determined by delimiters. -->
        <xsl:param name="delimiters"/>
        <xsl:param name="s"/>
        <xsl:if test="string-length($s)>0">
            <xsl:choose>
                <xsl:when test="string-length($delimiters)>0">
                    <!-- Use the FP convention of x:xs for recurring on a list -->
                    <xsl:variable name="d" select="substring($delimiters, 1, 1)"/>
                    <xsl:variable name="ds" select="substring($delimiters, 2)"/>
                    <xsl:call-template name="cap-words">
                        <xsl:with-param name="delimiters" select="$ds"/>
                        <xsl:with-param name="s">
                            <!-- substring-before and -after return the empty string if the delimiter isn't in the string -->
                            <xsl:choose>
                                <xsl:when test="contains($s, $d)">
                                    <xsl:value-of select="substring-before($s, $d)"/>
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:value-of select="$s"/>
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:with-param>
                    </xsl:call-template>
                    <xsl:call-template name="cap-words">
                        <xsl:with-param name="delimiters" select="$ds"/>
                        <xsl:with-param name="s" select="substring-after($s, $d)"/>
                    </xsl:call-template>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:call-template name="cap-first">
                        <xsl:with-param name="s" select="$s"/>
                    </xsl:call-template>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:if>
    </xsl:template>


    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>


    <xsl:template name="capitalize">
        <!-- Capitalize all the letters in a string -->
        <xsl:param name="s"/>
        <xsl:value-of select="translate($s, $lower, $upper)"/>
    </xsl:template>

    <xsl:template name="cap-first">
        <!-- Capitalize the first letter in a string -->
        <xsl:param name="s"/>
        <!-- Use the FP convention of x:xs for recurring on a list -->
        <xsl:variable name="x" select="substring($s, 1, 1)"/>
        <xsl:variable name="xs" select="concat(substring($s, 2), '_')"/>
        <xsl:call-template name="capitalize">
            <xsl:with-param name="s" select="$x"/>
        </xsl:call-template>
        <xsl:value-of select="$xs"/>
    </xsl:template>

    <!-- replace all block alias with the M1 class -->

    <xsl:template match="block[@class]|container[@class]">
        <xsl:copy>
            <xsl:variable name="moduleName" select="substring-before(node()|@*[name()='class'],'/')"/>
            <xsl:variable name="blockClass" select="substring-after(node()|@*[name()='class'],'/')"/>
            <xsl:variable name="blockClassCamel">
                <xsl:call-template name="cap-words">
                    <xsl:with-param name="delimiters" select="translate($blockClass, $alnum, '')"/>
                    <xsl:with-param name="s" select="$blockClass"/>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="aliasNodePrefix" select="$aliasesDoc/json/block/*[name()=$moduleName]"/>

            <xsl:choose>
                <xsl:when test="string-length($aliasNodePrefix)>0">
                    <xsl:attribute name="class">
                        <xsl:value-of select="concat($aliasNodePrefix, '_', substring($blockClassCamel, 1, string-length($blockClassCamel) - 1))"/>
                    </xsl:attribute>
                    <xsl:apply-templates select="node()|@*[name()!='class']"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:apply-templates select="node()|@*"/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:copy>
    </xsl:template>


</xsl:stylesheet>
