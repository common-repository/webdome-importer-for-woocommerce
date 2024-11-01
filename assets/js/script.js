document.addEventListener('DOMContentLoaded', function() {

    // Holen Sie sich alle Pfeil-Elemente innerhalb der Navigation
    var arrows = document.querySelectorAll('.wdwi-nav-container .nav-level-1');
    // Fügt jedem Pfeil-Element einen Klick-Eventlistener hinzu
    arrows.forEach(function(arrow) {
        arrow.addEventListener('click', function(event) {
            
            // Das übergeordnete <a>-Tag
            var parentLink = arrow.parentNode
    
            var grandParentLink = parentLink.parentNode
			
            var navigationLevels = grandParentLink.querySelectorAll('.nav')
			// Prüft, ob die Klasse 'open' bereits vorhanden ist
            if (parentLink.classList.contains('open')) {
                parentLink.classList.remove('open');
            } else {
                navigationLevels.forEach(function(navLevel){
                    const isOpen = navLevel.classList.contains("open")
                    if(isOpen){
                        var secondLevel = navLevel.querySelector('.nav-level-2')
                        secondLevel.style.display = "none"
                        navLevel.classList.remove("open")
         				var secondLevelIcon = navLevel.querySelector('.rotate-element img')
						console.log(secondLevelIcon)
						secondLevelIcon.classList.remove("rotated")
					
					}
                })
                parentLink.classList.add('open');
                
            }
    
            var icon  = this.querySelector('.rotate-element img')
            icon.classList.toggle("rotated")
    
            // Das nächste <ul>-Element, das die untergeordneten Kategorien enthält
            var childList = parentLink.querySelector('.nav-level-2');
            // Wechselt die Sichtbarkeit der untergeordneten Liste
            if (childList.style.display === 'none' || childList.style.display === '') {
                childList.style.display = 'block';
            } else {
                childList.style.display = 'none';
            }
    
        });
    });

    var arrows2 = document.querySelectorAll('.wdwi-nav-container .nav-level-2');
    // Fügt jedem Pfeil-Element einen Klick-Eventlistener hinzu
    arrows2.forEach(function(arrow) {
        arrow.addEventListener('click', function(event) {
            
            // Das übergeordnete <a>-Tag
            var parentLink = arrow.parentNode
    
            var grandParentLink = parentLink.parentNode
			
            var navigationLevels = grandParentLink.querySelectorAll('.nav')
			// Prüft, ob die Klasse 'open' bereits vorhanden ist
            if (parentLink.classList.contains('open')) {
                parentLink.classList.remove('open');
            } else {
                navigationLevels.forEach(function(navLevel){
                    const isOpen = navLevel.classList.contains("open")
                    if(isOpen){
                        var secondLevel = navLevel.querySelector('.nav-level-3')
                        secondLevel.style.display = "none"
                        navLevel.classList.remove("open")
         				var secondLevelIcon = navLevel.querySelector('.rotate-element img')
						console.log(secondLevelIcon)
						secondLevelIcon.classList.remove("rotated")
					
					}
                })
                parentLink.classList.add('open');
                
            }
    
            var icon  = this.querySelector('.rotate-element img')
            icon.classList.toggle("rotated")
    
            // Das nächste <ul>-Element, das die untergeordneten Kategorien enthält
            var childList = parentLink.querySelector('.nav-level-3');
            // Wechselt die Sichtbarkeit der untergeordneten Liste
            if (childList.style.display === 'none' || childList.style.display === '') {
                childList.style.display = 'block';
            } else {
                childList.style.display = 'none';
            }
    
        });
    });
    
});