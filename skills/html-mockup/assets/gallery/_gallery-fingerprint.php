<?php
/**
 * _gallery-fingerprint.php — the ONE definition of what the gallery was built from.
 *
 * Required by two callers that must never disagree:
 *   - `_build-gallery.php`, which STAMPS the digest into the output it writes;
 *   - `framework-audit.php`, which RECOMPUTES it from disk and compares (RT_GALLERY_STALE).
 *
 * WHY IT IS ONE FILE AND NOT TWO FUNCTIONS. framework-audit/SKILL.md states the rule plainly:
 * two implementations of one rule drift, and the hand-rolled one loses. A fingerprint is the
 * worst possible candidate for a second copy, because both copies keep answering — they simply
 * answer different questions, and the disagreement reads exactly like staleness. So the audit
 * requires THIS file out of the tree it is auditing rather than carrying its own idea of the
 * input set: whatever the generator hashed, the audit hashes.
 *
 * WHAT COUNTS AS AN INPUT: a file whose BYTES the generator reads. That is the whole test, and
 * it is why `design-personalities.md` is absent below — `_build-gallery.php` asserts it exists
 * and then transcribes from it BY HAND, so its bytes never reach the output. A change there
 * leaves the output stale in the ordinary sense of the word but identical in the mechanical one,
 * and a fingerprint that claimed otherwise would be reporting a defect it cannot actually see.
 * Transcription drift is a different rule with a different verifier; this one does not pretend
 * to cover it.
 *
 * THIS FILE IS NOT ITS OWN INPUT, for the same reason. Editing the definition does not change a
 * single byte of the gallery, so hashing it would turn every edit here into a repo-wide FAIL over
 * output that is perfectly current — a false positive of the loudest kind. The GENERATOR is
 * hashed, because its source genuinely decides the output.
 *
 * PATHS ARE RECORDED RELATIVE TO `skills/`, never absolute: two checkouts in different
 * directories must produce the same digest, or the gate reports the checkout location as a
 * defect.
 *
 * BYTES ARE HASHED RAW, with no line-ending normalisation. index.html is untracked and always
 * built locally, so a digest is never transported between checkouts and never has to survive
 * git's CRLF translation. Normalising would also mean telling text from binary before hashing,
 * and getting that wrong on a .woff2 or a .webp is silent corruption of the very check.
 */

/** The token both sides look for. One literal, so neither can misspell it independently. */
const NM_GALLERY_FP_MARKER = 'gallery-input-fingerprint';

/**
 * Every file the generator reads, as `relative path => sha256 of its bytes`, sorted by path.
 *
 * A missing file is recorded as `absent` rather than skipped. Skipping would make an input's
 * DELETION invisible — the set would simply be one shorter, and one shorter is indistinguishable
 * from "this checkout never had it". Recording the absence means an input appearing or
 * disappearing moves the digest exactly like an edit does.
 *
 * $gallery_dir is the directory holding `_build-gallery.php`, passed in rather than taken from
 * __DIR__ so the audit can point it at the tree it was given with --root.
 */
function nm_gallery_input_manifest( $gallery_dir ) {
	$gallery_dir = rtrim( str_replace( '\\', '/', $gallery_dir ), '/' );
	$assets      = dirname( $gallery_dir );        // skills/html-mockup/assets
	$skills      = dirname( $gallery_dir, 3 );     // skills/

	/* Fixed paths first. These four are read by name, so their absence is a defect the digest
	   must carry rather than a set that happens to be smaller. */
	$files = array(
		$gallery_dir . '/_build-gallery.php',
		$gallery_dir . '/_gallery-images.md',
		$assets . '/fonts/_fonts.php',
		$skills . '/ux-design-system/references/design-tokens.md',
	);

	/* Then the globbed sets, in the order the generator reaches them. `*.woff2` is here because
	   `_fonts.php` base64-embeds those bytes into the page: the font FILES are as much an input
	   as the registry that names them, and swapping one for a different cut would otherwise
	   change the rendered gallery without moving the digest. */
	foreach (
		array(
			$gallery_dir . '/img/*.webp',
			$assets . '/fonts/*.woff2',
			$skills . '/web-templates/references/templates/corporate/TPL-*.md',
			$skills . '/web-templates/references/templates/ecommerce/TPL-*.md',
		) as $pattern
	) {
		$hits = glob( $pattern );
		if ( is_array( $hits ) ) {
			foreach ( $hits as $hit ) {
				$files[] = str_replace( '\\', '/', $hit );
			}
		}
	}

	$manifest = array();
	foreach ( $files as $file ) {
		$rel = ( 0 === strpos( $file, $skills . '/' ) ) ? substr( $file, strlen( $skills ) + 1 ) : $file;
		/* ksort below, not the discovery order: glob() is documented to sort, but "documented to
		   sort" across three platforms and two filesystems is a promise this digest does not need
		   to borrow when one ksort() makes it its own. */
		$manifest[ $rel ] = is_file( $file ) ? hash_file( 'sha256', $file ) : 'absent';
	}
	ksort( $manifest, SORT_STRING );
	return $manifest;
}

/** The digest itself: sha256 over `path<space>hash` lines, one per input, newline-terminated. */
function nm_gallery_input_digest( $gallery_dir ) {
	$lines = '';
	foreach ( nm_gallery_input_manifest( $gallery_dir ) as $rel => $hash ) {
		$lines .= $rel . ' ' . $hash . "\n";
	}
	return hash( 'sha256', $lines );
}

/** The exact line the generator stamps into index.html. */
function nm_gallery_fingerprint_line( $digest ) {
	return '<!-- ' . NM_GALLERY_FP_MARKER . ': sha256:' . $digest . ' -->';
}

/**
 * The digest a built index.html records, or '' when it records none.
 *
 * '' and a digest are deliberately different answers rather than one "not current": an output
 * that predates the fingerprint and one built from the wrong inputs need different sentences,
 * because they need different reactions from the reader.
 */
function nm_gallery_embedded_digest( $html ) {
	if ( preg_match( '/<!--\s*' . NM_GALLERY_FP_MARKER . ':\s*sha256:([0-9a-f]{64})\s*-->/', $html, $m ) ) {
		return $m[1];
	}
	return '';
}
