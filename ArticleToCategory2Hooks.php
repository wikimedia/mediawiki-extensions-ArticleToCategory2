<?php

use MediaWiki\MediaWikiServices;

class ArticleToCategory2Hooks {

	/**
	 * Add category to the new page
	 * The category name is escaped to prevent JavaScript injection
	 *
	 * @param string &$text The text to prefill edit form with
	 * @param Title &$title Title object representing the new page to be created
	 */
	public static function onEditFormPreloadText( &$text, &$title ) {
		$request = RequestContext::getMain()->getRequest();

		if ( $request->getVal( 'category' ) && $request->getInt( 'new' ) === 1 ) {
			$contLang = MediaWikiServices::getInstance()->getContentLanguage();
			$text = "\n\n[[" . $contLang->getNsText( NS_CATEGORY ) . ':' .
					htmlspecialchars( $request->getVal( 'category' ) ) . ']]';
		}
	}

	/**
	 * Function to get the excluded categories list (blacklist)
	 * The list is retrieved from [[MediaWiki:Add Article to Category 2 excluded categories]] page.
	 *
	 * @return array Array of excluded categories, if any
	 */
	public static function getExcludedCategories() {
		$request = RequestContext::getMain()->getRequest();

		$excludedCategories = [];
		$specialcatpage = 'Add Article to Category 2 excluded categories';

		if ( $request->getVal( 'action' ) == 'edit' ) {
			return $excludedCategories;
		}

		$revisionLookup = MediaWikiServices::getInstance()->getRevisionLookup();
		$rev = $revisionLookup->getRevisionByTitle( Title::makeTitle( NS_MEDIAWIKI, $specialcatpage ) );
		if ( $rev ) {
			$revContent = $rev->getContent();
			$content = $revContent instanceof TextContent ? $revContent->getText() : '';

			if ( $content != '' ) {
				$changed = false;
				$c = explode( "\n", $content );

				foreach ( $c as $entry ) {
					if ( $entry[0] == ';' ) {
						$cat = trim( substr( $entry, 1 ) );
						$excludedCategories[] = $cat;
					}
				}
			}
		}

		return $excludedCategories;
	}

	/**
	 * Generate the input box
	 *
	 * @param CategoryPage $catpage The category article
	 * @return bool True to do the default behavior of CategoryPage::view
	 */
	public static function onCategoryPageView( $catpage ) {
		global $wgArticleToCategory2ConfigBlacklist, $wgScript;

		$context = $catpage->getContext();
		$title = $catpage->getTitle();
		$user = $context->getUser();

		if ( class_exists( 'MediaWiki\Permissions\PermissionManager' ) ) {
			// MW 1.33+
			$permManager = MediaWikiServices::getInstance()->getPermissionManager();
			if ( !$permManager->quickUserCan( 'edit', $user, $title ) ||
				!$permManager->quickUserCan( 'create', $user, $title ) ) {
				return true;
			}
		} else {
			if ( !$title->quickUserCan( 'edit' ) || !$title->quickUserCan( 'create' ) ) {
				return true;
			}
		}

		if ( !$user->isAllowed( 'ArticleToCategory2' ) ) {
			return true;
		}

		if ( $wgArticleToCategory2ConfigBlacklist ) {
			$excludedCategories = self::getExcludedCategories();
			foreach ( $excludedCategories as $value ) {
				if ( $title->getText() == $value ) {
					return true;
				}
			}
		}

		$templateParser = new TemplateParser( __DIR__ );
		$form = $templateParser->processTemplate(
			'add-category-form',
			[
				'action' => $wgScript,
				'allowed' => $user->isAllowed( 'ArticleToCategory2AddCat' ),
				'categoryName' => $title->getText(),
				'boxText' => $context->msg( 'articletocategory2-create-article-under-category-text' )->text(),
				'bText' => $context->msg( 'articletocategory2-create-article-under-category-button' )->text(),
				'boxText2' => $context->msg( 'articletocategory2-create-category-under-category-text' )->text(),
				'bText2' => $context->msg( 'articletocategory2-create-category-under-category-button' )->text()
			]
		);

		$context->getOutput()->addModules( 'ext.articletocategory2' );
		$context->getOutput()->addHTML( $form );

		return true;
	}
}
