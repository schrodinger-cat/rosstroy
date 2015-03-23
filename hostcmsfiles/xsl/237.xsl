<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокЭлементовИнфосистемы -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem"/>
	</xsl:template>
	
	<xsl:template match="/informationsystem">
		<xsl:if test="count(informationsystem_item) != 0">
			<script>
				$('#example, body').vegas({
					delay: 10000,
	    			slides: [
	    				<xsl:apply-templates select="informationsystem_item"/>
	    		    ]
				});
			</script>
		</xsl:if>		
	</xsl:template>
	
	<xsl:template match="informationsystem_item">
		{src: '<xsl:value-of select="dir"/><xsl:value-of select="image_large"/>'}
		<xsl:if test="position() != last()">,</xsl:if>
	</xsl:template>
	
</xsl:stylesheet>