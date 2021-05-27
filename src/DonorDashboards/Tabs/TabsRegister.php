<?php

namespace Give\DonorDashboards\Tabs;

use Give\DonorDashboards\Tabs\Contracts\Tab;
use Give\DonorDashboards\Exceptions\MissingTabException;
use Give\DonorDashboards\Exceptions\DuplicateTabException;

/**
 * @since 2.10.0
 */
class TabsRegister {
	/**
	 * FQCN of Tab classes
	 *
	 * @since 2.10.0
	 *
	 * @var string[]
	 */
	private $tabs = [];

	/**
	 * Returns all of the registered tabs
	 *
	 * @since 2.10.0
	 *
	 * @return string[]
	 */
	public function getTabs() {
		return $this->tabs;
	}

	/**
	 * Checks to see if a tab is registered with the given ID
	 *
	 * @since 2.10.0
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function hasTab( $id ) {
		return isset( $this->tabs[ $id ] );
	}

	/**
	 * Returns a tab with the given ID
	 *
	 * @since 2.10.0
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	public function getTab( $id ) {
		if ( ! $this->hasTab( $id ) ) {
			throw new MissingTabException( $id );
		}

		return $this->tabs[ $id ];
	}

	/**
	 * Returns all of the registered tab ids
	 *
	 * @since 2.10.0
	 *
	 * @return string[]
	 */
	public function getRegisteredIds() {
		return array_keys( $this->tabs );
	}

	/**
	 * Add a tab to the list of tabs
	 *
	 * @since 2.10.0
	 *
	 * @param string $tabClass FQCN of the Tab Class
	 */
	public function addTab( $tabClass ) {
		if ( ! is_subclass_of( $tabClass, Tab::class ) ) {
			throw new \InvalidArgumentException( 'Class must extend the ' . Tab::class . ' class' );
		}

		$tabId = $tabClass::id();

		if ( $this->hasTab( $tabId ) ) {
			throw new DuplicateTabException();
		}

		$this->tabs[ $tabId ] = $tabClass;

	}

	/**
	 * Helper for adding a bunch of tabs at once
	 *
	 * @since 2.10.0
	 *
	 * @param string[] $tabClasses
	 */
	public function addTabs( array $tabClasses ) {
		foreach ( $tabClasses as $tabClass ) {
			$this->addTab( $tabClass );
		}
	}

	public function registerTabRoutes() {
		foreach ( give()->donorDashboardTabs->tabs as $tabClass ) {
			$tab = new $tabClass;
			$tab->registerRoutes();
		}
	}

	public function enqueueTabAssets() {
		foreach ( give()->donorDashboardTabs->tabs as $tabClass ) {
			( new $tabClass )->enqueueAssets();
		}
	}
}
