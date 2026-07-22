const fs = require('fs');

const files = [
  'DashboardPage.vue',
  'ProfilePage.vue',
  'ResumesPage.vue',
  'JobPostingsPage.vue',
  'ApplicationsPage.vue',
  'CoverLettersPage.vue',
  'AnalysisPage.vue',
  'InterviewPrepPage.vue',
  'CareerRecommendationsPage.vue',
  'NotificationsPage.vue',
  'SettingsPage.vue',
  'BillingPage.vue'
];

files.forEach(file => {
  const path = 'resources/js/pages/' + file;
  if (!fs.existsSync(path)) return;
  
  let content = fs.readFileSync(path, 'utf8');
  
  // Inject import
  if (!content.includes('PageLayout.vue')) {
    content = content.replace(/<script setup lang="ts">/, '<script setup lang="ts">\nimport PageLayout from \'../components/layout/PageLayout.vue\'');
  }

  // Dashboard Page uses <div class="page-header">
  if (file === 'DashboardPage.vue') {
    content = content.replace(/<template>\s*<div>\s*<!-- Page header -->\s*<div class="page-header">\s*<div>\s*<h1 class="page-title">(.*?)<\/h1>\s*<p class="page-subtitle">(.*?)<\/p>\s*<\/div>\s*<div style="display:flex;gap:8px">\s*(.*?)\s*<\/div>\s*<\/div>/s, 
      '<template>\n  <PageLayout title="$1" subtitle="$2">\n    <template #actions>\n      $3\n    </template>')
      .replace(/<\/div>\s*<\/template>/, '  </PageLayout>\n</template>');
  }
  // Other pages use <div class="fade-in space-y-6"> and then a div with h1 and p
  else {
    // Regex for:
    // <div class="fade-in space-y-6">
    //   <div class="flex items-center justify-between"> (optional)
    //     <div>
    //       <h1 class="text-2xl font-bold text-white">Title</h1>
    //       <p class="text-slate-400 text-sm mt-0.5">Subtitle</p>
    //     </div>
    //   </div> (optional)
    
    // Some pages don't have the flex container:
    // <div class="fade-in space-y-6">
    //   <div>
    //     <h1 class="text-2xl font-bold text-white">Title</h1>
    //     <p class="text-slate-400 text-sm mt-0.5">Subtitle</p>
    //   </div>
    
    const regex1 = /<div class="fade-in[^\"]*">\s*<div class="flex items-center justify-between">\s*<div>\s*<h1 class="[^"]*">(.*?)<\/h1>\s*<p class="[^"]*">(.*?)<\/p>\s*<\/div>\s*<\/div>/s;
    const regex2 = /<div class="fade-in[^\"]*">\s*<div>\s*<h1 class="[^"]*">(.*?)<\/h1>\s*<p class="[^"]*">(.*?)<\/p>\s*<\/div>/s;
    const regex3 = /<div class="fade-in[^\"]*">\s*<div class="flex items-center justify-between">\s*<div>\s*<h1 class="[^"]*">(.*?)<\/h1>\s*<\/div>\s*<\/div>/s;
    const regex4 = /<div class="fade-in[^\"]*">\s*<div>\s*<h1 class="[^"]*">(.*?)<\/h1>\s*<\/div>/s;
    
    let matched = false;
    if (regex1.test(content)) {
      content = content.replace(regex1, '<PageLayout title="$1" subtitle="$2">');
      matched = true;
    } else if (regex2.test(content)) {
      content = content.replace(regex2, '<PageLayout title="$1" subtitle="$2">');
      matched = true;
    } else if (regex3.test(content)) {
      content = content.replace(regex3, '<PageLayout title="$1">');
      matched = true;
    } else if (regex4.test(content)) {
      content = content.replace(regex4, '<PageLayout title="$1">');
      matched = true;
    }
    
    if (matched) {
      content = content.replace(/<\/div>\s*<\/template>/, '  </PageLayout>\n</template>');
      
      // Let's also wrap the entire template correctly:
      content = content.replace(/<template>\s*<PageLayout/, '<template>\n  <PageLayout');
    }
  }

  fs.writeFileSync(path, content);
  console.log(file + ' updated');
});
