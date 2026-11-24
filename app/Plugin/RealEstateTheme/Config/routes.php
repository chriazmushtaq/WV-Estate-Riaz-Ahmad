<?php
Router::connect('/admin/real_estate_themes', array('plugin' => 'RealEstateTheme', 'controller' => 'RealEstateThemes', 'action' => 'index', 'admin' => true));
Router::connect('/admin/real_estate_themes/settings', array('plugin' => 'RealEstateTheme', 'controller' => 'RealEstateThemes', 'action' => 'settings', 'admin' => true));
