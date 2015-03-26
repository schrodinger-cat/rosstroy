<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ВыводЕдиницыИнформационнойСистемы  -->
	
	<xsl:template match="/">
		<div class="r-content">
            <div class="r-content__close"></div>

            <div class="r-content__text">
				<xsl:apply-templates select="/informationsystem/informationsystem_item"/>
			</div>
		</div>
	</xsl:template>
	
	<xsl:template match="/informationsystem/informationsystem_item">
		<div class="r-news__elem">
			<div class="r-news__text">
				
				<span class="r-news__link">
					<xsl:value-of disable-output-escaping="yes" select="name"/>
				</span>
				
				<xsl:value-of disable-output-escaping="yes" select="text"/>
				
			</div>
			
			<xsl:if test="count(property_value) != 0">
				<div class="r-news__photos">
					<xsl:if test="image_large != ''">
						<a href="{dir}{image_large}" class="fancybox" rel="group">
							<img src="{dir}{image_large}" class="r-news__main-photo" alt="{name}"/>	
						</a>
					</xsl:if>

					<xsl:apply-templates select="property_value" mode="photos">
						<xsl:with-param name="is_dir"><xsl:value-of select="dir"/></xsl:with-param>
					</xsl:apply-templates>
				</div>
			</xsl:if>
						
		</div>
	</xsl:template>
	
	<xsl:template match="property_value" mode="photos">
		<xsl:param name="is_dir" />
		<a href="{$is_dir}{file}" class="fancybox" rel="group">
			<img src="{$is_dir}{file_small}" class="r-news__img r-news__img_portrets" alt="{file_name}">
				<xsl:if test="position() mod 2 = 0">
					<xsl:attribute name="class">r-news__img r-news__img_portrets r-portrets_nomargin</xsl:attribute>
				</xsl:if>
			</img>
		</a>
	</xsl:template>
	
</xsl:stylesheet>