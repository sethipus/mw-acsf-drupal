Drupal.behaviors.searchFilterBehaviour = {
  attach(context) {
    const searchFilterContainer = context.querySelector('.search-filter-container');
    const searchFilterOpenButton = context.querySelector('.search-filter-open-button');
    const clearAllButtons = context.querySelectorAll('.search-filter-block__button--clear-all');
    const applyFiltersButtons = context.querySelectorAll('.search-filter-block__button--apply');
    const filters = context.querySelectorAll('.filter-block');

    filters.forEach(filter => {
      filter.addEventListener('click', () => {
       filter.classList.toggle('filter-block--open');
      });
    })

    filter.addEventListener('click', (event) => {
        event.target.classList.toggle('filter-block--open');
    });

    searchFilterOpenButton.addEventListener('click', function(event) {
      const searchFilterBlock = context.querySelector('.search-filter-block');

      searchFilterBlock.classList.add('search-filter-block--opened');
    });

    searchFilterContainer.addEventListener('click', function(event) {
      const target = event.target;

      switch (true) {
        case target.classList.contains('search-filter-header__close'):
          target.closest('.search-filter-block').classList.remove('search-filter-block--opened');
          break;
        case target.classList.contains('checkbox-item__input'):
          updateCounters();
          break;
      }
    });

    clearAllButtons.forEach(function (button) {
      button.addEventListener('click', function(event) {
        searchFilterContainer.querySelectorAll('.checkbox-item__input:checked').forEach(function (input) {
          input.checked = false;
        });

        event.preventDefault();
        updateCounters();

        const searchQuery = context.querySelector('.search-input__field').value;
        if (searchQuery !== '') {
          document.location.search = 'search=' + searchQuery;
        }
        else {
          document.location.search = '';
        }
        
      });
    });

    applyFiltersButtons.forEach(function (button) {
      button.addEventListener('click', function(event) {
        event.preventDefault();
        let queryElements = [];
        let appliedFilters = [];
        let appliedIds = [];
        const filterBlocks = context.querySelectorAll('.filter-block');
        const searchQuery = context.querySelector('.search-input__field').value;
        if (searchQuery !== '') {
          queryElements.push('search=' + searchQuery);
        }
  
        filterBlocks.forEach(function(element) {
          const inputLabels = element.querySelectorAll('.checkbox-item__input:checked + label');
          const inputElements = element.querySelectorAll('.checkbox-item__input:checked');
  
          inputLabels.forEach(function(label) {
            appliedFilters.push(label.innerText);
          });
          inputElements.forEach(function(input) {
            appliedIds.push(input.getAttribute('id'));
          });
          if (appliedIds.length > 0) {
            queryElements.push(element.getAttribute('data-filter') + '=' +  appliedIds.join(','));
            appliedIds = [];
          }
        });
        document.location.search = queryElements.join('&');
      });
    });

    const updateCounters = () => {
      let appliedFilters = [];
      const filterBlocks = context.querySelectorAll('.filter-block');
      const appliedFiltersBlock = context.querySelector('.search-filter-info__applied');
      const appliedFiltersCount = context.querySelector('.search-filter-info__applied-count');
      const appliedFiltersList = context.querySelector('.search-filter-info__applied-text');
      const clearAllButton = context.querySelector('.search-filter-info .search-filter-block__button--clear-all');
      
      filterBlocks.forEach(function(element) {
        const counterElement = element.querySelector('.filter-title__counter');
        const inputLabels = element.querySelectorAll('.checkbox-item__input:checked + label');
        let counter = inputLabels.length;
        counterElement.innerHTML = counter ? counter : '';
        inputLabels.forEach(function(label) {
          appliedFilters.push(label.innerText);
        });
      });

      if (appliedFilters.length) {
        appliedFiltersBlock.classList.remove('search-filter-info__applied--hidden');
        clearAllButton.classList.remove('search-filter-block__button--hidden');
      } else {
        appliedFiltersBlock.classList.add('search-filter-info__applied--hidden');
        clearAllButton.classList.add('search-filter-block__button--hidden');
      }

      appliedFiltersCount.innerHTML = appliedFilters.length;
      appliedFiltersList.innerHTML = appliedFilters.join(', ');
    }
  },
};
