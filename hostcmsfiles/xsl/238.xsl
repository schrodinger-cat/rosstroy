<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокЭлементовИнфосистемы -->
	<xsl:param name="columns" select="6"/>
	
	<xsl:template match="/">
		<xsl:if test="count(//informationsystem_item) != 0">
			<xsl:if test="count(//informationsystem_item) &gt; $columns">
				<div class="r-portrets__prev"></div>
				<div class="r-portrets__next"></div>
			</xsl:if>			
			
			<div class="r-portrets__slider">
				<xsl:apply-templates select="//informationsystem_item[(position() - 1) mod $columns = 0]" mode="first"/>
			</div>
		</xsl:if>
		
	</xsl:template>
	
	<xsl:template match="informationsystem_item" mode="first">
		<div class="r-portrets__wrap">
			<xsl:apply-templates select=".|following-sibling::informationsystem_item[position() &lt; $columns]"/>
			<xsl:if test="count(following-sibling::informationsystem_item) &lt; ($columns - 1)">
				<xsl:call-template name="emptycell">
					<xsl:with-param name="cells" select="$columns - 1 - count(following-sibling::informationsystem_item)"/>
				</xsl:call-template>
			</xsl:if>
		</div>
	</xsl:template>
	
	<xsl:template match="informationsystem_item">
		<a href="{url}" class="r-portrets__elem">
			<xsl:if test="position() mod 2 = 0">
				<xsl:attribute name="class">r-portrets__elem r-portrets_nomargin</xsl:attribute>
			</xsl:if>
			
			<xsl:if test="image_small != ''">
				<img class="r-portrets__photo" src="{dir}{image_small}" alt="{name}"/>
			</xsl:if>
			
			<div class="r-portrets__text">
				<div class="r-portrets__name">
					<xsl:value-of disable-output-escaping="yes" select="name"/>
				</div>
				
				<xsl:value-of disable-output-escaping="yes" select="description"/>
			</div>
		</a>
	</xsl:template>
	
	<xsl:template name="emptycell">
		<xsl:param name="cells"/>
		
		<xsl:if test="$cells &gt; 1">
			<xsl:call-template name="emptycell">
				<xsl:with-param name="cells" select="$cells - 1"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	
</xsl:stylesheet>