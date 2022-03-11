function focusinRemoveErrmsg (delegation, errmsg) {
  delegation.addEventListener('focusin', () => {
    if (!errmsg.classList.contains('hidden')) {
      errmsg.classList.add('hidden')
    }
  })
}
