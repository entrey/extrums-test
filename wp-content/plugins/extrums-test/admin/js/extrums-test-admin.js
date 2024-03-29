'use strict';

document.addEventListener('DOMContentLoaded', (e) => {
	new ExtrumsManager()
})

class ExtrumsManager {

	constructor() {
		this.defineVariables()
		this.setListeners()
		this.enableForms()
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
			this.queryForm.addEventListener('submit', this.handleQueryFormSubmit.bind(this))
		}
	}

	setPostsTableListeners() {
		setSubmitListener.call(this)

		function setSubmitListener() {
			this.postsTable.addEventListener('submit', this.handlePostsTableSubmit.bind(this))
		}
	}

	enableForms() {
		document
			.querySelectorAll('[type="submit"][disabled]')
			.forEach((submit) => submit.removeAttribute('disabled'))
	}

	handleQueryFormSubmit(e) {
		e.preventDefault()

		const keyword = this.queryForm.keywordInput.value.trim()

		if (!keyword) {
			return
		}

		this.queryForm.submitBtn.value = 'Downloading...'

		fetch(ajaxurl, {
			method: 'POST',
			body: getBody.call(this),
		})
			.then((response) => response.json())
			.then((posts) => updateResultsTable.call(this, posts))
			.catch((error) => console.error(`Error: ${error}`))
			.finally(() => {
				this.queryForm.keywordInput.value = ''
				this.queryForm.submitBtn.value = 'Search'
			})

		function getBody() {
			const body = new FormData()
			body.append('action', 'extrums_query_posts')
			body.append('nonce', this.container.nonce)
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

					newRow.dataset.postId = post.ID
					post.post_title && (newRow.querySelector('.title').textContent = post.post_title)
					post.post_content && (newRow.querySelector('.content').textContent = post.post_content)
					post.title && (newRow.querySelector('.meta-title').textContent = post.title)
					post.description && (newRow.querySelector('.meta-description').textContent = post.description)

					this.postsTable.tbody.insertAdjacentElement('beforeEnd', newRow)
				})
			}
		}
	}

	handlePostsTableSubmit(e) {
		e.preventDefault()

		const columnReplace = e.target.dataset.columnReplace
		const currentKeyword = this.titleKeyword.textContent
		const newKeywordInput = e.target.querySelector('[name="new-keyword"]')
		const replaceKeyword = newKeywordInput.value.trim()
		const submitBtn = e.target.querySelector('[type="submit"]')

		if (!columnReplace || !replaceKeyword) {
			return
		}

		submitBtn.value = 'Replacing...'

		fetch(ajaxurl, {
			method: 'POST',
			body: getBody.call(this),
		})
			.then((response) => response.json())
			.then((result) => updatePostsTable.call(this, result.data))
			.catch((error) => console.error(`Error: ${error}`))
			.finally(() => {
				newKeywordInput.value = ''
				submitBtn.value = 'Replace'
			})

		function getBody() {
			const body = new FormData()
			body.append('action', 'extrums_update_posts_data')
			body.append('nonce', this.container.nonce)
			body.append('column_replace', columnReplace)
			body.append('old_keyword', this.titleKeyword.textContent)
			body.append('new_keyword', replaceKeyword)
			body.append('posts', getPostsForReplace.call(this))
			return body
		}

		function getPostsForReplace() {
			const postIDs = []
			this.postsTable.tbody
				.querySelectorAll(`.table__column.${columnReplace}`)
				.forEach((td) => {
					if (!td.textContent.toLowerCase().includes(currentKeyword)) {
						return
					}
					const postId = td.closest('tr').dataset.postId
					return postIDs.push(postId)
				}, [])
			return postIDs
		}

		function updatePostsTable(posts) {
			if (!posts) {
				return
			}

			for (const ID in posts) {
				this.postsTable.tbody
					.querySelectorAll(`tr[data-post-id="${ID}"] .table__column.${columnReplace}`)
					.forEach((td) => td.textContent = posts[ID])
			}
		}
	}

}