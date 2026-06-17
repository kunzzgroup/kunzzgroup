// Performance Diagnostic Script for Web App Analysis
// Run this in Chrome DevTools Console to analyze DOM and event listener performance

(function() {
    'use strict';
    
    console.log('🔍 Starting Performance Analysis...\n');
    
    // Phase 1: DOM Analysis
    function analyzeDOM() {
        const allElements = document.querySelectorAll('*');
        const elementCount = allElements.length;
        
        // Count element types
        const elementTypes = {};
        allElements.forEach(el => {
            const tagName = el.tagName.toLowerCase();
            elementTypes[tagName] = (elementTypes[tagName] || 0) + 1;
        });
        
        // Sort by count
        const sortedTypes = Object.entries(elementTypes)
            .sort(([,a], [,b]) => b - a)
            .slice(0, 10);
        
        return {
            totalElements: elementCount,
            elementTypes: elementTypes,
            topTypes: sortedTypes
        };
    }
    
    // Phase 2: Event Listener Analysis
    function analyzeEventListeners() {
        const allElements = document.querySelectorAll('*');
        let totalListeners = 0;
        const elementListeners = new Map();
        
        // Get all elements with event listeners
        allElements.forEach(el => {
            const listeners = getEventListeners(el);
            if (listeners && Object.keys(listeners).length > 0) {
                let elementListenerCount = 0;
                const listenerTypes = [];
                
                Object.entries(listeners).forEach(([eventType, eventList]) => {
                    elementListenerCount += eventList.length;
                    if (eventList.length > 0) {
                        listenerTypes.push(`${eventType}(${eventList.length})`);
                    }
                });
                
                if (elementListenerCount > 0) {
                    totalListeners += elementListenerCount;
                    const selector = generateSelector(el);
                    elementListeners.set(selector, {
                        count: elementListenerCount,
                        types: listenerTypes,
                        element: el
                    });
                }
            }
        });
        
        // Sort by listener count
        const sortedListeners = Array.from(elementListeners.entries())
            .sort(([,a], [,b]) => b.count - a.count)
            .slice(0, 10);
        
        return {
            totalListeners,
            elementListeners: elementListeners,
            topElements: sortedListeners
        };
    }
    
    // Helper function to generate CSS selector
    function generateSelector(element) {
        if (element.id) {
            return `#${element.id}`;
        }
        
        let selector = element.tagName.toLowerCase();
        
        if (element.className) {
            const classes = element.className.split(' ').filter(c => c.trim());
            if (classes.length > 0) {
                selector += '.' + classes.join('.');
            }
        }
        
        // Add nth-child if needed for uniqueness
        const parent = element.parentElement;
        if (parent) {
            const siblings = Array.from(parent.children);
            const index = siblings.indexOf(element);
            if (index > 0) {
                selector += `:nth-child(${index + 1})`;
            }
        }
        
        return selector;
    }
    
    // Phase 3: Memory Impact Estimation
    function estimateMemoryImpact(domData, listenerData) {
        // Rough heuristics for memory estimation
        const baseElementMemory = 200; // bytes per element (rough estimate)
        const listenerMemory = 100; // bytes per listener (rough estimate)
        
        const domMemory = domData.totalElements * baseElementMemory;
        const listenerMemoryEst = listenerData.totalListeners * listenerMemory;
        
        return {
            domMemoryKB: Math.round(domMemory / 1024),
            listenerMemoryKB: Math.round(listenerMemoryEst / 1024),
            totalMemoryKB: Math.round((domMemory + listenerMemoryEst) / 1024)
        };
    }
    
    // Phase 4: Performance Scoring
    function calculatePerformanceScore(domData, listenerData) {
        let score = '✅ Good';
        const warnings = [];
        const suggestions = [];
        
        // DOM element thresholds
        if (domData.totalElements > 10000) {
            score = '❌ Too Heavy';
            warnings.push(`Excessive DOM elements: ${domData.totalElements}`);
            suggestions.push('Consider virtualizing large lists or lazy loading content');
        } else if (domData.totalElements > 5000) {
            score = '⚠️ Warning';
            warnings.push(`High DOM element count: ${domData.totalElements}`);
            suggestions.push('Review if all elements are necessary, consider lazy loading');
        }
        
        // Event listener thresholds
        if (listenerData.totalListeners > 1000) {
            score = score === '❌ Too Heavy' ? '❌ Critical' : '❌ Too Heavy';
            warnings.push(`Excessive event listeners: ${listenerData.totalListeners}`);
            suggestions.push('Use event delegation for repetitive elements');
        } else if (listenerData.totalListeners > 500) {
            if (score === '✅ Good') score = '⚠️ Warning';
            warnings.push(`High event listener count: ${listenerData.totalListeners}`);
            suggestions.push('Consider event delegation for similar elements');
        }
        
        // Check for problematic patterns
        const topTypes = domData.topTypes;
        if (topTypes.length > 0) {
            const [topType, topCount] = topTypes[0];
            if (topCount > domData.totalElements * 0.5) {
                suggestions.push(`High concentration of <${topType}> elements (${topCount}). Consider semantic HTML`);
            }
        }
        
        // Check for deeply nested elements
        const maxDepth = getMaxDepth();
        if (maxDepth > 15) {
            suggestions.push(`Deep DOM nesting detected (${maxDepth} levels). Consider flattening structure`);
        }
        
        return { score, warnings, suggestions };
    }
    
    function getMaxDepth() {
        let maxDepth = 0;
        
        function traverse(node, depth) {
            maxDepth = Math.max(maxDepth, depth);
            for (let child of node.children) {
                traverse(child, depth + 1);
            }
        }
        
        traverse(document.body, 0);
        return maxDepth;
    }
    
    // Phase 5: Generate Report
    function generateReport() {
        const domData = analyzeDOM();
        const listenerData = analyzeEventListeners();
        const memoryData = estimateMemoryImpact(domData, listenerData);
        const performanceData = calculatePerformanceScore(domData, listenerData);
        
        console.log('='.repeat(50));
        console.log('📊 PERFORMANCE ANALYSIS REPORT');
        console.log('='.repeat(50));
        
        console.log(`\n🏗️  DOM Analysis:`);
        console.log(`Total DOM elements: ${domData.totalElements.toLocaleString()}`);
        console.log(`\nTop 10 element types:`);
        domData.topTypes.forEach(([type, count], index) => {
            console.log(`${index + 1}. <${type}>: ${count.toLocaleString()}`);
        });
        
        console.log(`\n🎧 Event Listener Analysis:`);
        console.log(`Total Event Listeners: ${listenerData.totalListeners.toLocaleString()}`);
        console.log(`\nTop elements with most listeners:`);
        listenerData.topElements.forEach(([selector, data], index) => {
            console.log(`${index + 1}. ${selector} (${data.types.join(', ')})`);
        });
        
        console.log(`\n💾 Memory Impact (Estimated):`);
        console.log(`DOM Memory: ~${memoryData.domMemoryKB} KB`);
        console.log(`Listener Memory: ~${memoryData.listenerMemoryKB} KB`);
        console.log(`Total Estimated: ~${memoryData.totalMemoryKB} KB`);
        
        console.log(`\n📈 Performance Score: ${performanceData.score}`);
        
        if (performanceData.warnings.length > 0) {
            console.log(`\n⚠️  Warnings:`);
            performanceData.warnings.forEach(warning => console.log(`• ${warning}`));
        }
        
        if (performanceData.suggestions.length > 0) {
            console.log(`\n💡 Optimization Suggestions:`);
            performanceData.suggestions.forEach(suggestion => console.log(`• ${suggestion}`));
        }
        
        // Additional recommendations based on patterns
        console.log(`\n🔧 Additional Recommendations:`);
        
        // Check for common performance issues
        const images = document.querySelectorAll('img');
        const unoptimizedImages = Array.from(images).filter(img => 
            !img.loading && !img.hasAttribute('loading')
        );
        if (unoptimizedImages.length > 10) {
            console.log('• Add loading="lazy" to images for better performance');
        }
        
        const scripts = document.querySelectorAll('script');
        const blockingScripts = Array.from(scripts).filter(script => 
            !script.async && !script.defer && !script.hasAttribute('type="module"')
        );
        if (blockingScripts.length > 5) {
            console.log('• Consider using async/defer for non-critical scripts');
        }
        
        const stylesheets = document.querySelectorAll('link[rel="stylesheet"]');
        const renderBlockingCSS = Array.from(stylesheets).filter(link => 
            !link.media || link.media === 'all'
        );
        if (renderBlockingCSS.length > 3) {
            console.log('• Consider critical CSS inlining and non-critical CSS loading');
        }
        
        console.log('\n' + '='.repeat(50));
        console.log('✅ Analysis Complete! Copy this report for optimization phase.');
        console.log('='.repeat(50));
        
        return {
            domData,
            listenerData,
            memoryData,
            performanceData
        };
    }
    
    // Run the analysis
    try {
        const results = generateReport();
        
        // Store results globally for further analysis
        window.performanceAnalysisResults = results;
        
        console.log('\n💾 Results stored in window.performanceAnalysisResults for further analysis');
        
    } catch (error) {
        console.error('❌ Error during analysis:', error);
        console.log('Make sure you\'re running this in Chrome DevTools with the page fully loaded');
    }
})();
