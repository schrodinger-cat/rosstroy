<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:variable name="current_structure_id" select="/site/current_structure_id"/>

	<xsl:template match="/site">
		<div class="r-menu">
			<xsl:apply-templates select="structure[show=1]" />
		</div>
	
		<xsl:choose>
			<xsl:when test="structure[@id = $current_structure_id]/parent_id = 0">
				<div class="r-menu__inner">
					<xsl:apply-templates select="structure[@id = $current_structure_id]/structure" mode="second"/>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<xsl:variable name="parent" select="//structure[@id = $current_structure_id]/parent_id" />

				<div class="r-menu__inner">
					<xsl:apply-templates select="structure[@id = $parent]/structure" mode="second"/>
				</div>
			</xsl:otherwise>
		</xsl:choose>

		
	</xsl:template>	
	
	<xsl:template match="structure">
		<a href="{link}" class="r-menu__elem">
			<xsl:if test="$current_structure_id = @id or structure/@id = $current_structure_id">
				<xsl:attribute name="class">r-menu__elem r-menu__elem_active</xsl:attribute>	
			</xsl:if>

			<xsl:value-of disable-output-escaping="yes" select="name"/>
		</a>
	</xsl:template>

	<xsl:template match="structure" mode="second">
		<a href="{link}" class="r-menu__inner-elem">
			<xsl:if test="@id = $current_structure_id">
				<xsl:attribute name="class">r-menu__inner-elem r-menu__inner-elem_active</xsl:attribute>	
			</xsl:if>
			
			<xsl:value-of disable-output-escaping="yes" select="name"/>
		</a>
	</xsl:template>
</xsl:stylesheet>