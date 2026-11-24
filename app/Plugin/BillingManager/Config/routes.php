<?php
// Frontend Billing Portal Routes
Router::connect('/billing', array('plugin' => 'BillingManager', 'controller' => 'BillingPortals', 'action' => 'check_bill'));
Router::connect('/billing/check_bill', array('plugin' => 'BillingManager', 'controller' => 'BillingPortals', 'action' => 'check_bill'));
Router::connect('/billing/history', array('plugin' => 'BillingManager', 'controller' => 'BillingPortals', 'action' => 'history'));
Router::connect('/billing/portal', array('plugin' => 'BillingManager', 'controller' => 'BillingPortals', 'action' => 'client_portal'));
Router::connect('/billing/reader', array('plugin' => 'BillingManager', 'controller' => 'BillingPortals', 'action' => 'meter_reader'));
Router::connect('/billing/view_bill/*', array('plugin' => 'BillingManager', 'controller' => 'BillingPortals', 'action' => 'view_bill'));
Router::connect('/billing/submit_payment', array('plugin' => 'BillingManager', 'controller' => 'BillingPortals', 'action' => 'submit_payment'));

// Admin Prefix Routes
Router::connect('/admin/BillingManager', array('plugin' => 'BillingManager', 'controller' => 'BillingDashboards', 'action' => 'index', 'admin' => true));
Router::connect('/admin/billing_manager', array('plugin' => 'BillingManager', 'controller' => 'BillingDashboards', 'action' => 'index', 'admin' => true));
Router::connect('/admin/BillingManager/:controller', array('plugin' => 'BillingManager', 'action' => 'index', 'admin' => true));
Router::connect('/admin/BillingManager/:controller/:action/*', array('plugin' => 'BillingManager', 'admin' => true));
Router::connect('/admin/billing_manager/:controller', array('plugin' => 'BillingManager', 'action' => 'index', 'admin' => true));
Router::connect('/admin/billing_manager/:controller/:action/*', array('plugin' => 'BillingManager', 'admin' => true));

// Non-admin Direct Routes
Router::connect('/BillingManager', array('plugin' => 'BillingManager', 'controller' => 'BillingDashboards', 'action' => 'index', 'admin' => true));
Router::connect('/billing_manager', array('plugin' => 'BillingManager', 'controller' => 'BillingDashboards', 'action' => 'index', 'admin' => true));
Router::connect('/BillingManager/:controller', array('plugin' => 'BillingManager', 'action' => 'index', 'admin' => true));
Router::connect('/BillingManager/:controller/:action/*', array('plugin' => 'BillingManager', 'admin' => true));
Router::connect('/billing_manager/:controller', array('plugin' => 'BillingManager', 'action' => 'index', 'admin' => true));
Router::connect('/billing_manager/:controller/:action/*', array('plugin' => 'BillingManager', 'admin' => true));
