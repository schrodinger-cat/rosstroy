<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ВыводЕдиницыИнформационнойСистемы  -->
	
	<xsl:template match="/">
		<div class="r-content r-content_projects">
			<div class="r-content__close r-content__close_contacts"></div>
			<xsl:apply-templates select="/informationsystem/informationsystem_item"/>
		</div>

		<div class="r-projects__controls">
			Просмотр:
			
			<img src="/images/site/arrow_left.jpg" class="r-projects__left" alt="arrow left"/>

			<xsl:apply-templates select=".//informationsystem_item/property_value" mode="controls">
				<xsl:with-param name="dir" select=".//informationsystem_item/dir"/>
    		</xsl:apply-templates>

    		<img src="/images/site/arrow_right.jpg" class="r-projects__right" alt="arrow right"/>

    		<img src="/images/site/hide.jpg" class="r-projects__hide" alt="hide all"/>
		</div>

		<script>

			$('body').vegas({
				delay: 10000,
				autoplay: false,
    			slides: [
    				<xsl:apply-templates select=".//informationsystem_item/property_value" mode="images">
    					<xsl:with-param name="dir" select=".//informationsystem_item/dir"/>
    				</xsl:apply-templates>
    		    ],
    		    walk: function (index) {
    		    	index++;
    		    	$('.r-projects__thumbs').removeClass('r-projects__thumbs_active');
			        $('.r-projects__thumbs_'+index).addClass('r-projects__thumbs_active');
			    }
			});

			$('.r-projects__left').on('click', function(){
				$('body').vegas('previous');
			});

			$('.r-projects__right').on('click', function(){
				$('body').vegas('next');
			});

			$('.r-projects__thumbs').on('click', function(){
				slide = $(this).attr('data-slide');
				slide--;

				$('body').vegas('jump', slide);
			});
		</script>
	</xsl:template>
	
	<xsl:template match="/informationsystem/informationsystem_item">
		<div class="r-projects__title">
			<xsl:value-of disable-output-escaping="yes" select="name"/>
		</div>

		<xsl:value-of disable-output-escaping="yes" select="text"/>
	</xsl:template>

	<xsl:template match="property_value" mode="images">
		<xsl:param name="dir"/>
		{src: '<xsl:value-of select="$dir"/><xsl:value-of select="file"/>'}
		<xsl:if test="position() != last()">,</xsl:if>
	</xsl:template>

	<xsl:template match="property_value" mode="controls">
		<xsl:param name="dir"/>
		
		<div class="r-projects__thumbs r-projects__thumbs_{position()}" data-slide="{position()}">
			<xsl:if test="position() = 1">
				<xsl:attribute name="class">r-projects__thumbs r-projects__thumbs_active r-projects__thumbs_<xsl:value-of select="position()"/></xsl:attribute>	
			</xsl:if>

			<div class="r-projects__overflow"></div>
			<img src="{$dir}{file_small}" alt="{file_name}"/>
		</div>
	</xsl:template>
	

</xsl:stylesheet>