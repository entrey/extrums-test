'use strict';

document.addEventListener('DOMContentLoaded', (e) => {
	new ExtrumsManager()
})

class ExtrumsManager {

	constructor() {
		this.defineVariables()
		this.setListeners()
	}

	defineVariables() {
		this.container = document.querySelector('.extrums')
		this.container.nonce = this.container.dataset.nonce
		this.defineQueryFormVariables()
		this.defineResultTableVariables()
	}

	defineQueryFormVariables() {
		this.queryForm = this.container.querySelector('.query-form')
		this.queryForm.keywordInput = this.queryForm.querySelector('#post-keyword')
		this.queryForm.submitBtn = this.queryForm.querySelector('[type="submit"]')
	}

	defineResultTableVariables() {
		this.titleKeyword = this.container.querySelector('.title .keyword')
		this.postsTable = this.container.querySelector('.result__table')
		this.postsTable.tbody = this.postsTable.querySelector('tbody')
		this.postsTable.rowTemplate = getRowTemplate('tr.template')

		function getRowTemplate() {
			const template = document.querySelector('tr.template')
			const tr = template.cloneNode(true)
			tr.classList.remove('template')
			template.remove()
			return tr
		}
	}

	setListeners() {
		this.setQueryFormListeners()
		this.setPostsTableListeners()
	}

	setQueryFormListeners() {
		setSubmitListener.call(this)

		function setSubmitListener() {
			this.queryForm.addEventListener('submit', handleSubmitEvent.bind(this))
		}

		function handleSubmitEvent(e) {
			e.preventDefault()

			const keyword = this.queryForm.keywordInput.value.trim()

			if (!keyword) {
				return
			}

			this.queryForm.submitBtn.value = 'Downloading...'

			fetch(ajaxurl, {
				method: 'POST',
				body: getBody(),
			})
				.then((response) => response.json())
				.then((posts) => updateResultsTable.call(this, posts))
				.catch((error) => console.error('Error: ', error))
				.finally(() => {
					this.queryForm.keywordInput.value = ''
					this.queryForm.submitBtn.value = 'Search'
				})

			function getBody() {
				const body = new FormData()
				body.append('action', 'extrums_query_posts')
				body.append('keyword', keyword)
				return body
			}

			function updateResultsTable(posts) {
				updateTitleKeyword.call(this)
				clearRows.call(this)
				renderNewRows.call(this)

				function updateTitleKeyword() {
					this.titleKeyword.textContent = keyword
				}

				function clearRows() {
					this.postsTable.tbody.textContent = ''
				}

				function renderNewRows() {
					posts.forEach((post) => {
						const newRow = this.postsTable.rowTemplate.cloneNode(true)

						post.post_title && (newRow.querySelector('.title').textContent = post.post_title)
						post.post_content && (newRow.querySelector('.content').textContent = post.post_content)
						post.title && (newRow.querySelector('.meta-title').textContent = post.title)
						post.description && (newRow.querySelector('.meta-description').textContent = post.description)

						this.postsTable.tbody.insertAdjacentElement('beforeEnd', newRow)
					})
				}
			}
		}
	}

	setPostsTableListeners() {
		setSubmitListener.call(this)

		function setSubmitListener() {
			this.postsTable.addEventListener('submit', handleSubmitEvent.bind(this))
		}

		function handleSubmitEvent(e) {
			e.preventDefault()

			const columnReplace = e.target.dataset.columnReplace
			const replaceKeyword = e.target.querySelector('[name="new-keyword"]').value.trim()

			if (!columnReplace || !replaceKeyword) {
				return
			}

			this.postsTable.tbody
				.querySelectorAll(`.table__column.${columnReplace}`)
				.forEach((td) => {
					const currentKeyword = this.titleKeyword.textContent
					td.textContent = td.textContent.replace(new RegExp(currentKeyword, 'g'), replaceKeyword)
				})
		}
	}

}