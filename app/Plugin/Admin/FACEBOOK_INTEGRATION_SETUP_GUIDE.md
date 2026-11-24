# 📘 Facebook Lead Ads Integration & Access Token Generation Guide

## White Villas CRM System Documentation

This guide provides direct links and step-by-step instructions on how to generate your **Meta User / Page Access Token** and connect your **Facebook Business Pages** to the **White Villas CRM** for automated real-time lead ingestion.

---

## 🔗 Official Meta Direct Backlinks

1. 🔑 **Graph API Explorer (Get Token)**: [https://developers.facebook.com/tools/explorer/](https://developers.facebook.com/tools/explorer/)
2. 📱 **Meta Apps Dashboard**: [https://developers.facebook.com/apps/](https://developers.facebook.com/apps/)
3. 🧪 **Meta Lead Ads Testing Tool**: [https://developers.facebook.com/tools/lead-ads-testing](https://developers.facebook.com/tools/lead-ads-testing)
4. 💼 **Meta Business Suite**: [https://business.facebook.com/](https://business.facebook.com/)
5. 🛡️ **Access Token Debugger**: [https://developers.facebook.com/tools/debug/accesstoken/](https://developers.facebook.com/tools/debug/accesstoken/)
6. 📊 **Meta Ads Manager**: [https://adsmanager.facebook.com/](https://adsmanager.facebook.com/)

---

## 🔑 How to Get Your Meta Access Token (In 30 Seconds)

### Step 1: Open Graph API Explorer
* Visit [https://developers.facebook.com/tools/explorer/](https://developers.facebook.com/tools/explorer/) and log in with your Facebook account that manages your business page.

### Step 2: Select Your Meta App
* In the top-right corner dropdown labeled **"Meta App"**, choose your App (if none exists, create one at [developers.facebook.com/apps](https://developers.facebook.com/apps/)).

### Step 3: Select User or Page Token
* In the **"User or Page"** dropdown:
  * Select **"Get User Access Token"** (to discover all your pages at once), OR
  * Select your specific Facebook Page under **"Get Page Access Token"**.

### Step 4: Add the 5 Required Permissions
Click the **"Add a Permission"** dropdown and add these 5 permissions:
* ✅ `pages_show_list`
* ✅ `pages_read_engagement`
* ✅ `pages_manage_metadata`
* ✅ `pages_manage_ads`
* ✅ `leads_retrieval`

### Step 5: Click "Generate Access Token"
* Click the blue **"Generate Access Token"** button.
* Approve the Facebook permissions popup and select the page(s) you wish to manage.
* Copy the long token string generated in the **Access Token** field.

### Step 6: 1-Click Connect in White Villas CRM
1. Go to White Villas CRM ➔ **Facebook Integration** ➔ **API Accounts** (`/admin/facebook_integrations/settings`).
2. Click the blue button **"1-Click Connect Facebook Page"**.
3. Paste the token into the box and click **"Auto-Fetch My Pages"**.
4. The system will automatically list all your managed Facebook Pages with their Page ID and Name.
5. Click **"Connect & Auto-Sync Leads"** next to your desired page!

---

## ⚡ Webhook Details (Real-Time Ingestion)

* **Callback URL**: `https://yourdomain.com/whitevillas/api/facebook/webhook`
* **Verify Token**: `whitevillas_fb_verify_token_2026`
* **Subscription Field**: `leadgen`

---

## 🔄 1-by-1 Looping (Round-Robin) Agent Assignment

When new leads arrive from Facebook Lead Ads:
1. White Villas CRM automatically identifies the active sales agent who has waited the longest.
2. Assigns the lead to that agent and advances the loop (Agent 1 ➔ Agent 2 ➔ Agent 3 ➔ Agent 1...).
3. Automatically creates/links the `Client` and `Lead` record in CRM.

---

## 🕒 Background Scheduled Polling (Cron Job)

```bash
*/5 * * * * curl -s https://yourdomain.com/whitevillas/admin/facebook_integrations/cron_autofetch > /dev/null 2>&1
```