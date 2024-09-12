import ReadMore from '@stimulus-components/read-more'
import { useResize } from 'stimulus-use'

export default class extends ReadMore {
    static targets= [
        'content',
        'button',
    ]

    static values = {
        moreText: String,
        lessText: String,
    }

    connect() {
        this.buttonTarget.innerHTML = this.moreTextValue
        useResize(this)
        this.open = false
    }

    toggle(event) {
        this.open = !this.open
        this.buttonTarget.innerHTML = this.open ? this.lessTextValue : this.moreTextValue
        this.contentTarget.classList.toggle('text-ellipsis', !this.open)
    }

    resize() {
        if (!this.open) {
            this.buttonTarget.classList.toggle('d-none', this.contentTarget.scrollHeight <= this.contentTarget.clientHeight)
        }
    }
}
