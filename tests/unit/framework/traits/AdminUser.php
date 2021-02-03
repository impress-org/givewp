<?php

trait AdminUser {

	protected function setupAdminUser() {
		wp_set_current_user(
            $this->factory->user->create([ 'role' => 'administrator' ] )
        );
	}
} 