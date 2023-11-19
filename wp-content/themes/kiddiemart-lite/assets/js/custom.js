/**
 * Custom JS for theme elements
 */

/**
 * Wocommerce active class for category list
 */
let url = window.location.href;
const catLink = document.querySelectorAll(
  ".wc-block-product-categories-list li a"
);
catLink.forEach((item) => {
  if (item.href === url) {
    item.classList.add("active");
  }
});



// wrapping product category title and category link to a new div 

let catWrapper = document.querySelectorAll('.wp-block-featured-category .wc-block-featured-category__wrapper');

if (catWrapper !== null) {

  catWrapper.forEach((item) => {
    let btnWrap = document.createElement('div');
    btnWrap.classList.add('btn-wrap');
    item.appendChild(btnWrap);
    let catTitle = item.querySelectorAll('.wc-block-featured-category__title');
    catTitle.forEach((link) => {
      btnWrap.appendChild(link);
    })
    let catLink = item.querySelectorAll('.wc-block-featured-category__link');
    catLink.forEach((link) => {
      btnWrap.appendChild(link);
    })


  });
}

//  FOR HEADER PRODUCT SEARCH 

const searchInput = document.querySelector('.wp-header-right .search-product input[type="search"]');
const searchDummy = document.querySelector('.dummy-icon .wp-block-search__button');
const searchWrap = document.querySelector('.wp-header-right .search-popup');


const kiddiemartShowHandler = (e) => {
  e.preventDefault();
  let body = document.body;
  body.classList.toggle('search-toggle');
}

if (searchWrap !== null) {

  if (searchDummy !== null) {
    searchDummy.addEventListener('click', kiddiemartShowHandler)
  }

  document.addEventListener('click', function (e) {
    if (!searchWrap.contains(e.target) && !searchDummy.contains(e.target)) {
      let body = document.body;
      body.classList.remove('search-toggle')
    }
  }
  )
}
