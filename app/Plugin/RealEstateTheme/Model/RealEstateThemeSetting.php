<?php
App::uses('AppModel', 'Model');

class RealEstateThemeSetting extends AppModel {
    public $name = 'RealEstateThemeSetting';
    public $useTable = 'real_estate_theme_settings';

    public function ensureSchema() {
        try {
            $ds = $this->getDataSource();
            $fields = $this->schema();
            if (empty($fields)) {
                return;
            }

            if (!isset($fields['header_book_btn_text'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `header_book_btn_text` varchar(100) DEFAULT 'BOOK NOW' AFTER `sticky_header_mobile_enabled`");
            }
            if (!isset($fields['header_book_btn_link'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `header_book_btn_link` varchar(255) DEFAULT '#contact-form' AFTER `header_book_btn_text`");
            }
            if (!isset($fields['header_book_btn_new_tab'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `header_book_btn_new_tab` tinyint(1) NOT NULL DEFAULT 0 AFTER `header_book_btn_link`");
            }
            if (!isset($fields['hero_section_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `hero_section_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `hero_video_url`");
            }
            if (!isset($fields['intro_section_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `intro_section_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `intro_f2_btn_text`");
            }
            if (!isset($fields['properties_section_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `properties_section_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `resorts_title`");
            }
            if (!isset($fields['properties_count'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `properties_count` int(11) NOT NULL DEFAULT 4 AFTER `properties_section_enabled`");
            }
            if (!isset($fields['reasons_items'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `reasons_items` mediumtext DEFAULT NULL AFTER `reasons_title`");
            }
            if (!isset($fields['reasons_section_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `reasons_section_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `reasons_btn_text`");
            }
            if (!isset($fields['area_section_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `area_section_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `area_card3_image`");
            }
            if (!isset($fields['other_section_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `other_section_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `other_disclaimer`");
            }
            if (!isset($fields['contact_section_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `contact_section_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `contact_btn_text`");
            }
            if (!isset($fields['newsletter_section_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `newsletter_section_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `accordion_content`");
            }
            if (!isset($fields['amenities_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `amenities_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `hero_audio_btn_enabled`");
            }
            if (!isset($fields['amenities_super_title'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `amenities_super_title` varchar(255) DEFAULT 'WORLD-CLASS LIVING' AFTER `other_cta_link`");
            }
            if (!isset($fields['amenities_main_title'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `amenities_main_title` varchar(255) DEFAULT 'Premium Amenities & Modern Facilities' AFTER `amenities_super_title`");
            }
            if (!isset($fields['amenities_desc'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `amenities_desc` text DEFAULT NULL AFTER `amenities_main_title`");
            }
            if (!isset($fields['amenities_json'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `amenities_json` longtext DEFAULT NULL AFTER `amenities_desc`");
            }
            if (!isset($fields['ticker_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `ticker_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `hero_section_enabled`");
            }
            if (!isset($fields['ticker_badge_text'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `ticker_badge_text` varchar(150) DEFAULT '🔥 LATEST UPDATES' AFTER `ticker_enabled`");
            }
            if (!isset($fields['ticker_badge_bg'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `ticker_badge_bg` varchar(50) DEFAULT '#d11242' AFTER `ticker_badge_text`");
            }
            if (!isset($fields['ticker_badge_color'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `ticker_badge_color` varchar(50) DEFAULT '#ffffff' AFTER `ticker_badge_bg`");
            }
            if (!isset($fields['ticker_bg_color'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `ticker_bg_color` varchar(50) DEFAULT '#07152a' AFTER `ticker_badge_color`");
            }
            if (!isset($fields['ticker_text_color'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `ticker_text_color` varchar(50) DEFAULT '#ffffff' AFTER `ticker_bg_color`");
            }
            if (!isset($fields['ticker_text'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `ticker_text` longtext DEFAULT NULL AFTER `ticker_text_color`");
            }
            if (!isset($fields['ticker_speed'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `ticker_speed` int(11) NOT NULL DEFAULT 30 AFTER `ticker_text`");
            }
            if (!isset($fields['ticker_link'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `ticker_link` varchar(255) DEFAULT '' AFTER `ticker_speed`");
            }
            if (!isset($fields['ticker_show_on_all_pages'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `ticker_show_on_all_pages` tinyint(1) NOT NULL DEFAULT 0 AFTER `ticker_link`");
            }
            if (!isset($fields['ticker_mobile_hide_badge'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `ticker_mobile_hide_badge` tinyint(1) NOT NULL DEFAULT 1 AFTER `ticker_show_on_all_pages`");
            }
            if (!isset($fields['ticker_mobile_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `ticker_mobile_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `ticker_mobile_hide_badge`");
            }
            if (!isset($fields['sections_order'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `sections_order` varchar(255) DEFAULT 'hero,ticker,intro,properties,reasons,area,other,amenities,contact,newsletter' AFTER `newsletter_section_enabled`");
            }
            if (!isset($fields['btn_pdf_text'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `btn_pdf_text` varchar(100) DEFAULT 'Download Payment Plan (PDF)'");
            }
            if (!isset($fields['btn_pdf_pc_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `btn_pdf_pc_enabled` tinyint(1) NOT NULL DEFAULT 1");
            }
            if (!isset($fields['btn_pdf_mobile_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `btn_pdf_mobile_enabled` tinyint(1) NOT NULL DEFAULT 1");
            }
            if (!isset($fields['btn_whatsapp_text'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `btn_whatsapp_text` varchar(100) DEFAULT 'WhatsApp'");
            }
            if (!isset($fields['btn_whatsapp_pc_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `btn_whatsapp_pc_enabled` tinyint(1) NOT NULL DEFAULT 1");
            }
            if (!isset($fields['btn_whatsapp_mobile_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `btn_whatsapp_mobile_enabled` tinyint(1) NOT NULL DEFAULT 1");
            }
            if (!isset($fields['btn_call_text'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `btn_call_text` varchar(100) DEFAULT 'Call Owner'");
            }
            if (!isset($fields['btn_call_pc_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `btn_call_pc_enabled` tinyint(1) NOT NULL DEFAULT 1");
            }
            if (!isset($fields['btn_call_mobile_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `btn_call_mobile_enabled` tinyint(1) NOT NULL DEFAULT 1");
            }
            if (!isset($fields['card_agent_pc_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `card_agent_pc_enabled` tinyint(1) NOT NULL DEFAULT 1");
            }
            if (!isset($fields['card_agent_mobile_enabled'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `card_agent_mobile_enabled` tinyint(1) NOT NULL DEFAULT 1");
            }
            if (!isset($fields['card_agent_badge_text'])) {
                $this->query("ALTER TABLE `real_estate_theme_settings` ADD COLUMN `card_agent_badge_text` varchar(100) DEFAULT 'Verified Agent'");
            }

            // Ensure table is utf8mb4 for emojis
            $this->query("ALTER TABLE `real_estate_theme_settings` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (Exception $e) {}
    }

    public function defaultSectionsOrder() {
        return array('hero', 'ticker', 'intro', 'properties', 'reasons', 'area', 'other', 'amenities', 'contact', 'newsletter');
    }

    public function parseSectionsOrder($orderStr) {
        $defaults = $this->defaultSectionsOrder();
        if (empty($orderStr)) {
            return $defaults;
        }
        $raw = array_filter(array_map('trim', explode(',', (string)$orderStr)));
        $valid = array();
        foreach ($raw as $key) {
            if (in_array($key, $defaults, true) && !in_array($key, $valid, true)) {
                $valid[] = $key;
            }
        }
        foreach ($defaults as $key) {
            if (!in_array($key, $valid, true)) {
                $valid[] = $key;
            }
        }
        return $valid;
    }

    public function defaultAmenitiesList() {
        return array(
            array(
                'id' => 1,
                'title' => 'Commercial Hub & Retail',
                'description' => 'Boutique shopping avenues, daily grocery marts, convenience stores, and banking services within footsteps.',
                'icon' => 'fa-shopping-bag',
                'badge' => 'Commercial',
                'status' => 1
            ),
            array(
                'id' => 2,
                'title' => 'Fine Dining & Cafes',
                'description' => 'Multi-cuisine gourmet restaurants, rooftop dining lounges, executive coffee shops, and snack bars.',
                'icon' => 'fa-cutlery',
                'badge' => 'Dining',
                'status' => 1
            ),
            array(
                'id' => 3,
                'title' => '3D Cinema & Theater',
                'description' => 'State-of-the-art cinematic projection, immersive Dolby surround sound, and luxury recliner seating.',
                'icon' => 'fa-film',
                'badge' => 'Entertainment',
                'status' => 1
            ),
            array(
                'id' => 4,
                'title' => 'Gaming Zone & VR Arcade',
                'description' => 'High-tech gaming stations, virtual reality arenas, arcade classics, and e-sports hubs for youth & families.',
                'icon' => 'fa-gamepad',
                'badge' => 'Recreation',
                'status' => 1
            ),
            array(
                'id' => 5,
                'title' => 'Fitness Gym & Wellness Spa',
                'description' => 'Modern cardio & strength equipment, certified fitness trainers, sauna, steam rooms, and rejuvenating spa suites.',
                'icon' => 'fa-heartbeat',
                'badge' => 'Wellness',
                'status' => 1
            ),
            array(
                'id' => 6,
                'title' => 'Snooker & Billiards Club',
                'description' => 'Championship standard snooker & pool tables, private player lounges, and tournament-grade lighting.',
                'icon' => 'fa-circle',
                'badge' => 'Sports',
                'status' => 1
            ),
            array(
                'id' => 7,
                'title' => 'Central Heating & Climate Control',
                'description' => 'Energy-efficient heating systems, customized thermal insulation, and central climate control throughout the complex.',
                'icon' => 'fa-fire',
                'badge' => 'Comfort',
                'status' => 1
            ),
            array(
                'id' => 8,
                'title' => '24/7 Smart Security & CCTV',
                'description' => 'Multi-tier gated security, facial recognition access, AI perimeter surveillance, and dedicated patrol guards.',
                'icon' => 'fa-shield',
                'badge' => 'Security',
                'status' => 1
            ),
            array(
                'id' => 9,
                'title' => 'Solar Power & 100% Backup',
                'description' => 'Zero-downtime dual electricity backup with cutting-edge solar power integration and heavy-duty quiet generators.',
                'icon' => 'fa-sun-o',
                'badge' => 'Eco Energy',
                'status' => 1
            ),
            array(
                'id' => 10,
                'title' => 'Swimming Pool & Jacuzzi',
                'description' => 'Temperature-controlled indoor & outdoor pools, kids splash zones, and poolside relaxation cabanas.',
                'icon' => 'fa-tint',
                'badge' => 'Leisure',
                'status' => 1
            ),
            array(
                'id' => 11,
                'title' => 'Executive Business Lounge',
                'description' => 'High-speed fiber Wi-Fi, private meeting rooms, video conference facilities, and collaborative workstations.',
                'icon' => 'fa-briefcase',
                'badge' => 'Business',
                'status' => 1
            ),
            array(
                'id' => 12,
                'title' => 'Community Mosque',
                'description' => 'Spacious air-conditioned mosque with dedicated ablution facilities and peaceful Islamic architectural design.',
                'icon' => 'fa-moon-o',
                'badge' => 'Community',
                'status' => 1
            ),
            array(
                'id' => 13,
                'title' => 'Landscaped Parks & Play Area',
                'description' => 'Lush green botanical gardens, dedicated jogging tracks, open-air seating gazebos, and secure children play areas.',
                'icon' => 'fa-tree',
                'badge' => 'Nature',
                'status' => 1
            ),
            array(
                'id' => 14,
                'title' => 'High-Speed Lifts & Parking',
                'description' => 'Dedicated multi-level parking with EV charging stations and smart high-speed passenger & cargo elevators.',
                'icon' => 'fa-car',
                'badge' => 'Convenience',
                'status' => 1
            )
        );
    }

    public function decodeAmenities($jsonString = '') {
        if (!empty($jsonString)) {
            $decoded = json_decode($jsonString, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }
        return $this->defaultAmenitiesList();
    }

    public function ensureDefaultSettings() {
        $this->ensureSchema();
        try {
            $existing = $this->find('first', array('conditions' => array('RealEstateThemeSetting.id' => 1), 'recursive' => -1));
            if (!empty($existing['RealEstateThemeSetting'])) {
                $rec = $existing['RealEstateThemeSetting'];
                if (empty($rec['amenities_json'])) {
                    $rec['amenities_json'] = json_encode($this->defaultAmenitiesList());
                    $this->save(array('RealEstateThemeSetting' => array('id' => 1, 'amenities_json' => $rec['amenities_json'])), false);
                }
                return $rec;
            }

            $defaults = array(
                'id' => 1,
                'top_header_enabled' => 1,
                'sticky_header_enabled' => 1,
                'sticky_header_mobile_enabled' => 1,
                'header_book_btn_text' => 'BOOK NOW',
                'header_book_btn_link' => '#contact-form',
                'header_book_btn_new_tab' => 0,
                'hero_audio_btn_enabled' => 1,
                'hero_section_enabled' => 1,
                'intro_section_enabled' => 1,
                'properties_section_enabled' => 1,
                'reasons_section_enabled' => 1,
                'area_section_enabled' => 1,
                'other_section_enabled' => 1,
                'contact_section_enabled' => 1,
                'newsletter_section_enabled' => 1,
                'amenities_enabled' => 1,
                'brand_site_title' => 'WHITE VILLAS',
                'brand_tagline' => 'Luxury Villas & Commercial Booking Portal',
                'brand_phone' => '+92339 1101156',
                'brand_email' => 'info@whitealpinelodges.com',
                'brand_address' => '49-AA F/F Sector D Commercial Bahria Town Lahore, PAKISTAN',
                'brand_timing' => 'Mon - Sat: 9:00 AM - 6:00 PM',
                'hero_badge_text' => 'OFFICIAL BOOKING & PAYMENT PLANS',
                'hero_main_heading' => 'Exclusive Luxury Living & High-ROI Investment Portfolios',
                'hero_subheading' => 'Explore flexible 1 to 5 years installments, prime location plots, executive apartments & smart residential villas.',
                'hero_cta_text' => 'Download Payment Plans',
                'hero_cta_link' => '/paymentplans',
                'hero_video_url' => 'https://assets.mixkit.co/videos/preview/mixkit-modern-apartment-building-at-sunset-41484-large.mp4',
                'intro_super_title' => 'DISTINGUISHED REAL ESTATE',
                'intro_main_title' => 'Redefining Architectural Masterpieces in Prime Metropolitans',
                'intro_description' => 'White Villas is dedicated to delivering bespoke residential and commercial developments. Combining architectural brilliance with sustainable planning, we ensure long-term value appreciation and seamless lifestyle luxury for investors and homeowners.',
                'intro_cta_text' => 'Explore Properties',
                'intro_cta_link' => '/Properties',
                'intro_f1_title' => 'Architectural Elegance',
                'intro_f1_desc' => 'Crafted with premium Italian finishes, smart-home automation, and earthquake-resistant structural engineering.',
                'intro_f1_link' => '/Properties',
                'intro_f2_title' => 'High-Yield Investment',
                'intro_f2_desc' => 'Guaranteed rental returns, prompt delivery timelines, and flexible easy-installment payment milestones.',
                'intro_f2_link' => '/paymentplans',
                'properties_super_title' => 'FEATURED OPPORTUNITIES',
                'properties_main_title' => 'Explore Exclusive Plots, Commercial & Ready Villas',
                'properties_count' => 6,
                'properties_cta_text' => 'View All Listings',
                'properties_cta_link' => '/Properties',
                'reasons_super_title' => 'THE WHITE VILLAS ADVANTAGE',
                'reasons_main_title' => 'Why Discerning Investors Choose White Villas',
                'reasons_desc' => 'Our developments are engineered for maximum asset appreciation, uncompromised security, and timeless architectural aesthetics.',
                'reasons_r1_title' => 'Prime Approved Locations',
                'reasons_r1_desc' => 'Strategically situated near key business hubs, commercial avenues, and serene landscaped surroundings.',
                'reasons_r2_title' => 'Guaranteed Construction Timelines',
                'reasons_r2_desc' => 'Rigorous milestone-based construction schedule ensuring on-time possession without delays.',
                'reasons_r3_title' => 'Transparent Installment Plans',
                'reasons_r3_desc' => 'No hidden charges, comprehensive digital ledger tracking, and flexible installment schedules.',
                'area_super_title' => 'NEIGHBORHOOD AMENITIES',
                'area_main_title' => 'Everything You Need Within Arm’s Reach',
                'area_card1_title' => 'Elite Educational Institutes',
                'area_card1_dist' => '5 Min Distance',
                'area_card1_desc' => 'Top-tier international schools and higher educational campuses nearby.',
                'area_card2_title' => 'Commercial & Shopping Arenas',
                'area_card2_dist' => '8 Min Distance',
                'area_card2_desc' => 'Fine dining restaurants, luxury boutique retail brands, and supermarkets.',
                'area_card3_title' => 'Healthcare & Wellness Centers',
                'area_card3_dist' => '3 Min Distance',
                'area_card3_desc' => 'State-of-the-art hospitals, specialized clinics, and fitness centers.',
                'other_super_title' => 'EXPAND YOUR HORIZONS',
                'other_main_title' => 'Discover Exclusive Opportunities Across Our Projects',
                'other_desc' => 'Explore our portfolio of premier commercial malls, serviced apartments, and luxury villas designed for high capital growth.',
                'other_cta_text' => 'Request Portfolio',
                'other_cta_link' => '/contact',
                'amenities_super_title' => 'WORLD-CLASS LIVING',
                'amenities_main_title' => 'Premium Amenities & Modern Facilities',
                'amenities_desc' => 'Experience an extraordinary standard of living with cutting-edge recreational hubs, uninterrupted utilities, executive leisure facilities, and state-of-the-art wellness zones.',
                'amenities_json' => json_encode($this->defaultAmenitiesList()),
                'contact_super_title' => 'GET IN TOUCH WITH AN EXPERT',
                'contact_main_title' => 'Schedule a Private Site Tour or Consultation',
                'contact_desc' => 'Our senior property consultants are available to guide you through payment schedules, floor plans, and investment yields.',
                'newsletter_title' => 'Stay Ahead of the Real Estate Market',
                'newsletter_desc' => 'Subscribe to receive early-bird booking alerts, price adjustment notices, and quarterly investment outlooks.',
                'accordion_title' => 'Frequently Asked Questions',
                'footer_about_text' => 'White Villas helps clients explore published property listings, compare opportunities, and connect with the right team for verified real estate guidance.',
                'footer_copyright' => 'WHITE ALPINE LODGES © 2026',
                'footer_legal_name' => 'WHITE VILLAS PROPERTY PORTAL',
                'social_facebook' => 'https://facebook.com',
                'social_instagram' => 'https://instagram.com',
                'social_youtube' => 'https://youtube.com',
                'social_whatsapp' => '923074432729',
                'created' => date('Y-m-d H:i:s'),
                'updated' => date('Y-m-d H:i:s')
            );

            $this->create();
            $this->save(array('RealEstateThemeSetting' => $defaults), false);
            return $defaults;
        } catch (Exception $e) {
            return array();
        }
    }

    public function getSettings() {
        $this->ensureSchema();
        $data = $this->find('first', array('conditions' => array('RealEstateThemeSetting.id' => 1), 'recursive' => -1));
        if (!empty($data['RealEstateThemeSetting'])) {
            $rec = $data['RealEstateThemeSetting'];
            if (empty($rec['amenities_json'])) {
                $rec['amenities_json'] = json_encode($this->defaultAmenitiesList());
                $this->save(array('RealEstateThemeSetting' => array('id' => 1, 'amenities_json' => $rec['amenities_json'])), false);
            }
            return $rec;
        }
        return $this->ensureDefaultSettings();
    }
}
