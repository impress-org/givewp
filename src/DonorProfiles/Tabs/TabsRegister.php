<?php

namespace Give\DonorProfiles\Tabs;

use Give\DonorProfiles\Tabs\Contracts\Tab;
use http\Exception\InvalidArgumentException;

class TabsRegister {
	/**
	 * FQCN of Tab classes
	 *
	 * @since 2.11.0
	 *
	 * @var string[]
	 */
	private $tabs = [];

	/**
	 * Returns all of the registered tabs
	 *
	 * @since 2.11.0
	 *
	 * @return string[]
	 */
	public function getTabs() {
		return $this->tabs;
	}

	/**
	 * Checks to see if a tab is registered with the given ID
	 *
	 * @since 2.11.0
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
	 * @since 2.11.0
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	public function getTab( $id ) {
		if ( ! isset( $this->tabs[ $id ] ) ) {
			throw new \InvalidArgumentException( "No migration exists with the ID {$id}" );
		}

		return $this->migrations[ $id ];
	}

	/**
	 * Returns all of the registered tab ids
	 *
	 * @since 2.11.0
	 *
	 * @return string[]
	 */
	public function getRegisteredIds() {
		return array_keys( $this->tabs );
	}

	/**
	 * Add a tab to the list of tabs
	 *
	 * @since 2.11.0
	 *
	 * @param string $tabClass FQCN of the Tab Class
	 */
	public function addTab( $tabClass ) {
		if ( ! is_subclass_of( $tabClass, Tab::class ) ) {
			throw new \InvalidArgumentException( 'Class must extend the ' . Tab::class . ' class' );
		}

		$tabId = $tabClass::id();

		if ( isset( $this->tabs[ $tabId ] ) ) {
			throw new \InvalidArgumentException( 'A tab can only be added once. Make sure there are not id conflicts.' );
		}

		$this->tabs[ $tabId ] = $tabClass;

		error_log( 'in addTab: ' . serialize( $this->tabs ) );
	}

	/**
	 * Helper for adding a bunch of tabs at once
	 *
	 * @since 2.11.0
	 *
	 * @param string[] $tabClasses
	 */
	public function addTabs( array $tabClasses ) {
		foreach ( $tabClasses as $tabClass ) {
			$this->addTab( $tabClass );
		}
	}

	public function registerTabRoutes() {
		foreach ( give()->donorProfileTabs->tabs as $tabClass ) {
			$tab = new $tabClass;
			$tab->registerRoutes();
		}
	}

	public function enqueueTabAssets() {
		foreach ( $this->tabs as $tabClass ) {
			( new $tabClass )->enqueueAssets();
		}
	}
}
