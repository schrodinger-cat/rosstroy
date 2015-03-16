<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ВыводЕдиницыИнформационнойСистемы  -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem/informationsystem_item"/>
	</xsl:template>
	
	<xsl:template match="/informationsystem/informationsystem_item">
		<div class="r-news__elem">
			<div class="r-news__text">
				<div class="r-news__date">
					<xsl:value-of select="substring-before(date, '.')"/>
					<xsl:variable name="month_year" select="substring-after(date, '.')"/>
					<xsl:variable name="month" select="substring-before($month_year, '.')"/>
					<xsl:choose>
						<xsl:when test="$month = 1"> января </xsl:when>
						<xsl:when test="$month = 2"> февраля </xsl:when>
						<xsl:when test="$month = 3"> марта </xsl:when>
						<xsl:when test="$month = 4"> апреля </xsl:when>
						<xsl:when test="$month = 5"> мая </xsl:when>
						<xsl:when test="$month = 6"> июня </xsl:when>
						<xsl:when test="$month = 7"> июля </xsl:when>
						<xsl:when test="$month = 8"> августа </xsl:when>
						<xsl:when test="$month = 9"> сентября </xsl:when>
						<xsl:when test="$month = 10"> октября </xsl:when>
						<xsl:when test="$month = 11"> ноября </xsl:when>
						<xsl:otherwise> декабря </xsl:otherwise>
					</xsl:choose>
					<xsl:value-of select="substring-after($month_year, '.')"/><xsl:text> г.</xsl:text>
				</div>


				<span class="r-news__link">
					<xsl:value-of disable-output-escaping="yes" select="name"/>
				</span>

				<xsl:value-of disable-output-escaping="yes" select="text"/>

			</div>

			<xsl:if test="count(property_value) != 0">
				<div class="r-news__photos">
					<xsl:apply-templates select="property_value" mode="photos">
						<xsl:with-param name="is_dir"><xsl:value-of select="dir"/></xsl:with-param>
					</xsl:apply-templates>
				</div>
			</xsl:if>

			<div class="r-news__line r-news__line_inner"></div>
			
			<span class="r-news__share">Поделиться:</span>
			<script type="text/javascript" src="//yastatic.net/share/share.js" charset="utf-8"></script><div class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="small" data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,gplus" data-yashareTheme="counter"></div>
		</div>
	</xsl:template>

	<xsl:template match="property_value" mode="photos">
		<xsl:param name="is_dir" />
		<img src="{$is_dir}{file_small}" class="r-news__img" alt="{file_name}"/>
	</xsl:template>

</xsl:stylesheet>