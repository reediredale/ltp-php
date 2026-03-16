# Programmatic SEO System for Marketing to Home Services Businesses

## Overview

This system generates **27,144 local SEO landing pages** to market your services TO home services businesses across Brisbane suburbs. Each page is optimized for local search and follows The Brain Audit marketing framework by Sean D'Souza.

**You're selling marketing services (Lead Generation, Meta Ads, Google Ads, etc.) TO businesses like plumbers, electricians, and roofers.**

## How It Works

### Data Structure

The system uses three JSON files in the `/data` folder:

1. **marketing-services.json** - 6 marketing services you offer:
   - Lead Generation
   - Meta Ads
   - Google Ads
   - Conversion Rate Optimisation
   - Email Marketing
   - Strategy & Consulting

2. **business-types.json** - 26 types of home services businesses (your target customers):
   - Plumbers, Electricians, HVAC Companies
   - Roofers, Builders, Carpenters
   - And 20 more trade/home service businesses

3. **brisbane-suburbs.json** - 174 Brisbane suburbs:
   - West End, Fortitude Valley, Brisbane City
   - And 171 more Brisbane suburbs

### URL Pattern

Pages are generated using the pattern: `/{marketing-service}-for-{business-type}-in-{suburb}`

**Examples:**
- `/lead-generation-for-plumbers-in-fortitude-valley`
- `/meta-ads-for-electricians-in-west-end`
- `/google-ads-for-roofers-in-brisbane-city`
- `/conversion-optimization-for-hvac-companies-in-new-farm`
- `/email-marketing-for-builders-in-paddington`
- `/strategy-consulting-for-carpenters-in-toowong`

### Total Pages

- **6 marketing services** × **26 business types** × **174 suburbs** = **27,144 landing pages**

## The Brain Audit Structure

Each landing page follows Sean D'Souza's Brain Audit framework:

1. **The Problem** - Addresses marketing challenges specific to that business type (using data from business-types.json)
2. **The Solution** - Shows how the marketing service solves their problems (benefits from marketing-services.json)
3. **Target Profile** - Identifies ideal clients ($1M+ revenue businesses ready to invest in marketing)
4. **Objections** - Answers common questions (pricing, done-for-you vs advisory, timeframes)
5. **Uniqueness** - Why choose you (home services specialists, local knowledge, performance pricing)
6. **Testimonials** - Social proof section (ready for client testimonials)
7. **Pricing** - Transparent pricing model for each service
8. **Call to Action** - Free strategy session form and contact info

## Local SEO Optimization

### On-Page SEO
- **Title Tags**: "{Marketing Service} for {Business Type} in {Suburb}, Brisbane"
- **Meta Descriptions**: Optimized for business owners searching for marketing help
- **H1 Tags**: Service + Business Type + Location combination
- **Internal Linking**: Related services, other suburbs, other business types
- **Business-Specific Content**: Addresses marketing challenges unique to each industry

### Schema Markup
Each page includes ProfessionalService schema with:
- Service type (the marketing service)
- Target audience (the business type)
- Service area (suburb + Brisbane)
- Contact information
- Business hours

### Sitemap
The system auto-generates a complete sitemap at `/sitemap.xml` including:
- All static pages (excluding landing pages that start with `/lp-`)
- All 27,144 programmatic SEO pages
- Total: **27,150 URLs** in sitemap
- Proper priority and changefreq values

## Directory/Browse Page

Visit `/brisbane-home-services-marketing` to see a directory:
- Browse by marketing service category
- Browse by business/industry type
- See all Brisbane suburbs served

## Sitemap Details

The auto-generated sitemap is available at `/sitemap.xml`.

### Sitemap Structure

Since we have 27,150 total URLs (under Google's 50,000 URL limit), the sitemap uses a **sitemap index** that points to a single sitemap file:

**Main Sitemap Index:** `/sitemap.xml`
- This is what you submit to Google Search Console
- Points to `/sitemap-1.xml`

**Individual Sitemap:**
- `/sitemap-1.xml` - 27,150 URLs (all pages)

**Total: 27,150 URLs**

### What's Included

**Static Pages (6):**
- `/` (homepage)
- `/services`
- `/about`
- `/contact`
- `/thank-you-consult`
- `/brisbane-home-services-marketing`

**Programmatic SEO Pages (27,144):**
- All marketing-service-for-business-in-suburb combinations
- 6 services × 26 business types × 174 suburbs

**Excluded Pages:**
- `/lp-001` and any other landing pages starting with `/lp-`
- Landing pages are excluded as they're typically accessed through paid advertising campaigns

### How to Submit to Google

1. Go to Google Search Console
2. Submit: `http://ltp.test/sitemap.xml` (the main index)
3. Google will automatically discover and crawl the sitemap
4. You'll see all 27,150 URLs indexed in Search Console

## Adding More Data

### To Add Marketing Services:
Edit `/data/marketing-services.json`:
```json
{
  "slug": "new-service",
  "name": "New Service Name",
  "category": "Service Category",
  "description": "What this service does for businesses",
  "benefits": [
    "Benefit 1",
    "Benefit 2",
    "Benefit 3",
    "Benefit 4"
  ],
  "pricing_model": "How you price this service",
  "ideal_for": "Which businesses benefit most"
}
```

### To Add Business Types:
Edit `/data/business-types.json`:
```json
{
  "slug": "new-business-type",
  "name": "New Business Type (plural)",
  "singular": "Business Name (singular)",
  "industry": "Industry Name",
  "annual_revenue_range": "$XK - $XM+",
  "typical_services": ["Service 1", "Service 2", "Service 3"],
  "marketing_challenges": ["Challenge 1", "Challenge 2", "Challenge 3"],
  "ideal_customer": "Who this business typically serves"
}
```

### To Add Suburbs:
Edit `/data/brisbane-suburbs.json`:
```json
{
  "slug": "new-suburb-name",
  "name": "New Suburb Name",
  "postcode": "4000"
}
```

**Important**: After editing JSON files, verify syntax with:
```bash
php -r "json_decode(file_get_contents('data/marketing-services.json'));"
```

## URL Examples

Here are some real examples from your 27,144 pages:

**Lead Generation:**
- `/lead-generation-for-plumbers-in-west-end`
- `/lead-generation-for-electricians-in-fortitude-valley`
- `/lead-generation-for-roofers-in-brisbane-city`

**Meta Ads:**
- `/meta-ads-for-hvac-companies-in-new-farm`
- `/meta-ads-for-builders-in-paddington`
- `/meta-ads-for-carpenters-in-toowong`

**Google Ads:**
- `/google-ads-for-plumbers-in-indooroopilly`
- `/google-ads-for-electricians-in-coorparoo`
- `/google-ads-for-pest-control-in-woolloongabba`

**Other Services:**
- `/conversion-optimization-for-solar-installers-in-albany-creek`
- `/email-marketing-for-bathroom-renovators-in-ashgrove`
- `/strategy-consulting-for-cleaners-in-annerley`

## Technical Details

### Routing System
The system checks for marketing-business-suburb patterns in `index.php`:
1. Splits URL by `-for-` to separate marketing service
2. Splits remainder by `-in-` to separate business type and suburb
3. Validates all three parts exist in the JSON data
4. Loads the appropriate template with dynamic data
5. Returns 404 if no match found

### Template Location
Local marketing service pages use the `'local-marketing-service'` template in `index.php`

### Performance
- JSON data is loaded once per request
- Lookup arrays created for O(1) access
- No database required
- Fast response times

## SEO Best Practices Implemented

✅ Unique title and description for each page
✅ H1 tags with marketing service + business type + location
✅ ProfessionalService schema markup
✅ Internal linking to related services
✅ Internal linking to same service for different businesses
✅ Internal linking to same service/business in different suburbs
✅ Business-specific marketing challenges addressed
✅ Service-specific benefits highlighted
✅ Clear pricing models explained
✅ FAQ section addressing objections
✅ Mobile-responsive design
✅ Fast loading (no external dependencies)

## Marketing Framework Elements

Based on The Brain Audit by Sean D'Souza:

1. ✅ **Problem** - Identifies marketing challenges specific to each business type
2. ✅ **Solution** - Shows how marketing service solves those challenges
3. ✅ **Target Profile** - $1M+ revenue businesses ready to scale
4. ✅ **Objections** - FAQ addresses pricing, service model, timeframes, results
5. ✅ **Uniqueness** - Home services specialist, local knowledge, performance pricing
6. ✅ **Testimonials** - Section ready for social proof
7. ✅ **Guarantee** - Transparent pricing and flexible service models
8. ✅ **Price** - Clear pricing models for each service
9. ✅ **Call to Action** - Free strategy session, multiple contact methods

## Page Content Customization

Each page automatically customizes content based on:

**From Marketing Service Data:**
- Service name and description
- 4 key benefits
- Pricing model
- Ideal use case

**From Business Type Data:**
- Business name and industry
- Revenue range
- Typical services they offer
- 3 main marketing challenges
- Their ideal customers

**From Suburb Data:**
- Suburb name and postcode
- Local targeting for that area

## Next Steps for Optimization

1. **Add Real Case Studies** - Include specific results for each business type
2. **Add Pricing Ranges** - Where appropriate, add typical investment levels
3. **Add Testimonials** - Organize by business type for relevant social proof
4. **Add Photos/Videos** - Visual content of work with different business types
5. **Customize Problem Sections** - More specific challenges per business type
6. **Geo-Targeting** - Use actual coordinates for each suburb
7. **Google My Business** - Create GMB for key suburbs
8. **Backlinks** - Build citations and directory listings
9. **Content Marketing** - Blog posts linking to these pages
10. **Analytics** - Track which service-business-suburb combinations convert best

## Sample URLs to Test

Try these URLs in your browser (http://ltp.test):

```
/lead-generation-for-plumbers-in-west-end
/meta-ads-for-electricians-in-fortitude-valley
/google-ads-for-roofers-in-brisbane-city
/conversion-optimization-for-hvac-companies-in-new-farm
/email-marketing-for-builders-in-paddington
/strategy-consulting-for-carpenters-in-toowong
/brisbane-home-services-marketing (directory page)
/sitemap.xml (all 27K+ pages)
```

## Files Modified/Created

### Created:
- `data/marketing-services.json` - 31 marketing services
- `data/business-types.json` - 26 home services business types
- `PROGRAMMATIC-SEO-README.md` - This documentation

### Modified:
- `index.php` - Added routing, template, and data loading for new pattern

### Existing:
- `data/brisbane-suburbs.json` - 174 Brisbane suburbs (unchanged)

## Questions?

The system is designed to be easily expandable. Simply add more marketing services, business types, or suburbs to the JSON files and the system will automatically generate new pages and update the sitemap.

**Current Stats:**
- Marketing Services: 6
- Business Types: 26
- Suburbs: 174
- Total Pages: **27,144**
