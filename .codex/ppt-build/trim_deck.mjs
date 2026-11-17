import fs from 'node:fs/promises';
import { PresentationFile, FileBlob } from 'file:///C:/Program%20Files/WindowsApps/OpenAI.CodexPrimaryRuntime.v26-1112-737-0_26.1112.737.0_x64__3k8sg7r9htsxt/dependencies/node/node_modules/@oai/artifact-tool/dist/artifact_tool.mjs';

const source = 'C:/Users/prate/Downloads/ADVSQL Project FInal.pptx';
const buildDir = 'C:/Users/prate/OneDrive/Desktop/eventManagement/ppt-build';
const outputDir = 'C:/Users/prate/OneDrive/Desktop/eventManagement/output';
const presentation = await PresentationFile.importPptx(await FileBlob.load(source));
const sourcePresentation = await PresentationFile.importPptx(await FileBlob.load(source));
const sourceCover = await sourcePresentation.export({ slide: sourcePresentation.slides.getItem(0), format: 'png', scale: 1 });
await fs.writeFile(`${buildDir}/cover-source.png`, new Uint8Array(await sourceCover.arrayBuffer()));

// Keep the cover, then the clearest project overview, problem, architecture,
// workflow, database, dataset, modeling, evaluation, and reference slides.
const keptOriginalIndexes = new Set([0, 1, 2, 7, 13, 15, 16, 17, 20, 21]);
const pageNumberEdits = [
  ['sh/kredcr6t', '8', '4'],
  ['sh/j6947i9w', '16', '6'],
  ['sh/snedsvml', '17', '7'],
  ['sh/6hw3y9sb', '18', '8'],
  ['sh/kf2d8zi1', '21', '9'],
  ['sh/wnihgf2h', '22', '10'],
];
for (const [id, oldValue, value] of pageNumberEdits) {
  presentation.resolve(id).text.replace(oldValue, value);
}

const removeIndexes = Array.from({ length: 23 }, (_, index) => index)
  .filter(index => !keptOriginalIndexes.has(index))
  .sort((a, b) => b - a);
presentation.slides.delete(removeIndexes);

if (presentation.slides.items.length !== 10) {
  throw new Error(`Expected 10 slides, got ${presentation.slides.items.length}`);
}

for (const [index, slide] of presentation.slides.items.entries()) {
  const png = await presentation.export({ slide, format: 'png', scale: 1 });
  await fs.writeFile(`${buildDir}/slide-${String(index + 1).padStart(2, '0')}.png`, new Uint8Array(await png.arrayBuffer()));
}
const montage = await presentation.export({ format: 'png', montage: true, scale: 0.5 });
await fs.writeFile(`${buildDir}/montage.png`, new Uint8Array(await montage.arrayBuffer()));

const draft = await PresentationFile.exportPptx(presentation);
await draft.save(`${buildDir}/campus_placement_10_slide_draft.pptx`);
console.log(JSON.stringify({
  slides: presentation.slides.items.length,
  keptOriginalIndexes: [...keptOriginalIndexes].map(index => index + 1),
  draft: `${buildDir}/campus_placement_10_slide_draft.pptx`,
  montage: `${buildDir}/montage.png`,
}));
