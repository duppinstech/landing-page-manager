# Appendix: owner decisions batch review (2026-10-16)

Ready-to-use text from the departments. Linked from PLAN.md and the [meeting notes](2026-10-16-1034-owner-decisions-review.md).

## Smoke test steps 25–41
These add to steps 1–15 ([10-14b](2026-10-14-2245-p0-batch-review.md#smoke-test)), steps 16–24 ([10-16 appendix](2026-10-16-0009-maps-p1-batch-review-appendix.md#smoke-test-steps-1624)) and the [10-16 re-review corrections](2026-10-16-0022-maps-p1-batch-rereview.md#corrections-to-the-0009-appendix). Run them once, on top of the whole stack.

25. **(Corrected.)** A Whiterail AI page whose `reviews_html` holds only an iframe from a host that isn't allowed (for example `https://example.com/widget`) shows no Reviews section and loads no Swiper. A YouTube embed there shows the section and loads Swiper.
26. **Phone audit, before upgrading each live site.** Every line printed is a page whose displayed or dialled number will change:
    `wp eval 'foreach(get_posts(["post_type"=>"landing_page","post_status"=>"publish","numberposts"=>-1]) as $p){$t=get_the_terms($p->ID,"client");$c=$t?carbon_get_term_meta($t[0]->term_id,"client_phone"):"";$g=carbon_get_post_meta($p->ID,"client_tel");if($g!==""&&$g!==$c)echo "$p->ID\t$g\t$c\n";}'`
27. **Phone (QuickTow and Whiterail AI).**
    - Page phone set: the displayed number and the `tel:` link both use it.
    - Page phone empty: the client's number is used, and call buttons show.
    - Both empty: the validator flags the phone.
28. **Draft on publish (test on WP 6.2 and on 6.4 or later).**
    - A new page that's missing a keyword, published: it stays a draft, shows the warning, and the message says "Draft updated".
    - The same page with the fields filled in, published: it goes live.
    - A live page with a field cleared: it stays live and only shows the error.
    - Quick Edit setting "Published" on an incomplete draft: it stays a draft.
29. **Additional Scripts, on a site with a value saved before the upgrade.**
    - Before approval it doesn't print, and a notice appears on every plugin screen.
    - Approve it: it prints once, after the footer scripts, and a confirmation notice shows.
    - An admin edits it: the new value prints right away.
    - Clear it: the hash is deleted.
    - As an Editor (or a multisite site admin): the field is read-only, and a forged POST is rejected with a notice.
30. **Header Tracking Code.** As an Editor it's read-only, and saving other client fields leaves it unchanged. An admin can change it.
31. **Each live site:** run `wp eval 'var_dump(defined("DISALLOW_UNFILTERED_HTML")&&DISALLOW_UNFILTERED_HTML);'`. Any result other than `false` means admins can't edit raw-HTML fields there.
32. **QuickTow, logged out, "Tracking tags need jQuery" checkbox.**
    - Ticked: jQuery loads.
    - Unticked, with no markers in the code: it doesn't.
    - An approved Additional Script that uses jQuery: jQuery loads.
33. **QuickTow Styles 1, 2 and 3.**
    - Body classes are `quicktow--layout1`, `--layout2` and `--layout-3`.
    - `style-2-layout.css` loads with no 404s.
    - A client with Brandfetch colours uses them (darkened where needed). A default client looks unchanged.
    - The mobile call button shows a phone icon, is 44px or larger, and dials.
34. **Pro compatibility.**
    - Pro 1.0.2: core's `tracking.js` loads (no 404).
    - Pro 1.0.3: the Conversion Tracking tab shows, and Pro's own script loads.
    - Pro 1.0.4 branch: the tab is hidden, and the toggles are kept.
35. **Iframes on WP 6.2 or later.**
    - YouTube, Vimeo and Google Maps embeds render with `loading="lazy"`, a title, and fit a 375px screen.
    - `width="100%"` stays 100%.
    - Iframes from other hosts are removed.
    - On WP 5.9, all iframes are removed and nothing fatals.
36. **Fetch Reviews twice within 10 minutes.** The second attempt shows "fetched a moment ago", and the old `lpmanager_reviews_*` content transients are gone.
37. **Server-key notice.** "Hide for 30 days" hides it for that user only.
38. **Footer allowlist.** An mu-plugin callback named in `lpmanager_allowed_footer_callbacks` prints. Callbacks that aren't on the list don't.
39. **Purge the page cache and CDN after the upgrade.** Then reload a QuickTow Style 2 page and check the Network tab for 404s.
40. **Roll back to the 1.0.6 zip.**
    - No fatal error, and QuickTow keeps its styles.
    - Additional Scripts stop (expected).
    - Whiterail AI pages without a page phone lose their call buttons (expected in 1.0.6).
    - Re-upgrade: the scripts print again without re-approval.
41. **Uninstall (on staging).**
    - Removed: the Additional Scripts hash, the `lp_kept_draft_*` transients, the notice-dismissal user meta, and the `_lpmanager_client_template` term meta.
    - Kept: `_lpmanager_track_*` and `lpmanager_pro_*`.

## Release-note draft (marketing)
- **Your call-tracking numbers get the credit.** A page's own phone number now replaces the client's default in every template, both the number shown and the number dialled.
- **Conversion and chat scripts now run.** Code in Additional Scripts prints on every landing page once an administrator approves it. Only administrators can change it or a client's Header Tracking Code.
- **QuickTow matches each client's brand.** Pages use the client's colors and keep white text readable. The mobile call button now shows a phone icon.
- **Video and map embeds.** You can embed YouTube, Vimeo and Google Maps in rich-text fields.
- **Unfinished pages don't go live.** If a page you publish is missing required fields, it stays a draft and you're told why. Pages that are already live are never taken down.

## Support FAQ
1. **"My QuickTow pages changed color after the update."** QuickTow now uses the client's Primary and Secondary colors. To get the old look back, set them to `#002868` and `#b22234` on the client screen.
2. **"The call button shows or dials a different number."** The page's own phone number now wins over the client's Contact Phone. Clear the page's phone field to use the client's number.
3. **"My Additional Scripts or tracking pixel isn't firing."** As an administrator, open Landing Pages → Settings and click **Approve Additional Scripts**, then purge the page cache. If your host disables unfiltered HTML, a developer has to use the `lpmanager_landing_page_footer` action instead.
4. **"I can't edit Header Tracking Code any more."** Only administrators (super admins on multisite) can change it now.
5. **"My review widget or embed disappeared."** Only YouTube, Vimeo and Google Maps embed iframes are kept, and only on WordPress 6.2 or later. Use Fetch Reviews, or have a developer add your provider with `lpmanager_iframe_src_patterns`.
6. **"I clicked Publish but the page is a draft."** Required fields are missing, and the red notice lists them. Fill them in and publish again. Pages that are already live are never unpublished.
7. **"Fetch Reviews says 'try again in N minutes'."** Each client can fetch reviews once every 10 minutes, to keep Google charges down.
