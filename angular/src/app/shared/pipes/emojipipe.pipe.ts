import { Pipe, PipeTransform, Inject } from '@angular/core';
import { DOCUMENT } from '@angular/common';
import { emojis, EmojiService, CompressedEmojiData } from '@ctrl/ngx-emoji-mart/ngx-emoji';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';

@Pipe({
  name: 'emojipipe'
})
export class EmojipipePipe implements PipeTransform {
  private static cachedEmojiRegex: RegExp;
  private sheet: string;

  constructor(@Inject(DOCUMENT) private document: Document, private sanitizer: DomSanitizer, private emojiService: EmojiService) { }

  public transform(html: string, sheet = 'apple'): SafeHtml {
    this.sheet = sheet;
    return this.sanitizer.bypassSecurityTrustHtml(
      this.emojisToImages(html)
    );
  }

  /**
	 * Replaces all unicode emojis available through emoji-mart with a span displaying
	 * the image representation of that emoji
	 * @param html The original html
	 */
	public emojisToImages(html: string): string {
		// Ensure most html entities are parsed to unicode:
		const div = <Element> this.document.createElement('div');
		div.innerHTML = html;
		html = div.innerHTML;

		html = html
			// Replace zero width joins with their unicode representations:
			.replace(/&zwj;/g, '\u200d')

			// Replace every emoji with a span with a background image:
			.replace(this.emojiRegex, unicodeEmoji => {
				const hexCodeSegments = [];
				let i = 0;
				while (i < unicodeEmoji.length) {
					const segment = unicodeEmoji.codePointAt(i).toString(16).toUpperCase();
					hexCodeSegments.push(segment);

					i += Math.ceil(segment.length / 4);
				}
				const hexCode = hexCodeSegments.join('-');
				const matchingData = this.findEmojiData(hexCode);
				if (matchingData) {
					const span = document.createElement('span');
					span.style.width = '22px';
					span.style.height = '22px';
					span.style.display = 'inline-block';
					span.style.backgroundImage = `url(/images/emojis/${this.sheet}_32.png)`;
					span.style.backgroundSize = `${100 * 52}%`;
					const multiply = 100 / 51;
					span.style.backgroundPosition = `${multiply * matchingData.sheet[0]}% ${multiply * matchingData.sheet[1]}%`;
					span.style.textIndent = '-10000px';
					span.style.overflow = 'hidden';
					span.innerText = unicodeEmoji;


					return span.outerHTML;
				}

				return unicodeEmoji;
		});

		return html;
	}

	/**
	 * Regex matching all unicode emojis contained in emoji-mart
	 */
	private get emojiRegex(): RegExp {
		if (EmojipipePipe.cachedEmojiRegex) {
			return EmojipipePipe.cachedEmojiRegex;
		}

		let characterRegexStrings: string[] = [];
		for (const emoji of emojis) {
			characterRegexStrings.push(this.emojiService.unifiedToNative(emoji.unified).replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));

			if (emoji.skinVariations) {
				for (const skinVariation of emoji.skinVariations) {
					characterRegexStrings.push(this.emojiService.unifiedToNative(skinVariation.unified).replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
				}
			}
		}

		characterRegexStrings = characterRegexStrings.sort((a, b) => {
			if (a.length > b.length) {
				return -1;
			}

			if (b.length > a.length) {
				return 1;
			}

			return 0;
		});

		const strings = characterRegexStrings;
		const reString = '(' + strings.join('|') + ')';
		EmojipipePipe.cachedEmojiRegex = new RegExp(reString, 'gu');

		return EmojipipePipe.cachedEmojiRegex;
	}

	/**
	 * Find raw emoji-mart data for a specific emoji hex code
	 * @param hexCode String representation of the emoji hex code
	 */
	private findEmojiData(hexCode: string): CompressedEmojiData {
		for (const emojiData of emojis) {
			if (emojiData.unified === hexCode) {
				return emojiData;
			}

			if (emojiData.skinVariations) {
				for (const skinVariation of emojiData.skinVariations) {
					if (skinVariation.unified === hexCode) {
						const skinData = Object.assign({}, emojiData);
						skinData.sheet = skinVariation.sheet;
						return skinData;
					}
				}
			}
		}

		return null;
	}

}
