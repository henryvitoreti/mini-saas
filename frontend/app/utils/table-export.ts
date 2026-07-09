export type TableExportFormat = 'xls'|'pdf';

export type TableExportColumn = {
  name: string;
  label: string;
};

export type TableExportRecord = Record<string, string>;

const PDF_PAGE_WIDTH = 842;
const PDF_PAGE_HEIGHT = 595;
const PDF_PAGE_MARGIN = 28;
const PDF_TITLE_FONT_SIZE = 12;
const PDF_META_FONT_SIZE = 8;
const PDF_TABLE_FONT_SIZE = 7;
const PDF_HEADER_HEIGHT = 20;
const PDF_ROW_HEIGHT = 18;
const PDF_CELL_PADDING = 4;
const PDF_TITLE_BLOCK_HEIGHT = 38;

function escapeHtmlValue(value: string): string {
  return value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function normalizeSpreadsheetValue(value: string): string {
  if (/^[=+\-@]/.test(value)) {
    return `'${value}`;
  }

  return value;
}

function buildXlsContent(columns: TableExportColumn[], records: TableExportRecord[]): string {
  const headerCells = columns.map((column: TableExportColumn): string => {
    return `<th>${escapeHtmlValue(column.label)}</th>`;
  });
  const bodyRows = records.map((record: TableExportRecord): string => {
    const cells = columns.map((column: TableExportColumn): string => {
      const value = normalizeSpreadsheetValue(record[column.name] ?? '');

      return `<td style="mso-number-format:'\\@';">${escapeHtmlValue(value)}</td>`;
    });

    return `<tr>${cells.join('')}</tr>`;
  });

  return [
    '<html>',
    '<head>',
    '<meta charset="UTF-8">',
    '</head>',
    '<body>',
    '<table border="1">',
    `<thead><tr>${headerCells.join('')}</tr></thead>`,
    `<tbody>${bodyRows.join('')}</tbody>`,
    '</table>',
    '</body>',
    '</html>',
  ].join('');
}

function normalizePdfText(value: string): string {
  return (value || ' ')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^\x20-\x7E]/g, '?');
}

function getPdfTextLiteral(value: string): string {
  return normalizePdfText(value)
    .replace(/\\/g, '\\\\')
    .replace(/\(/g, '\\(')
    .replace(/\)/g, '\\)');
}

function getTruncatedText(value: string, maxLength: number): string {
  const normalizedValue = normalizePdfText(value);

  if (normalizedValue.length <= maxLength) {
    return normalizedValue;
  }

  if (maxLength <= 3) {
    return normalizedValue.slice(0, maxLength);
  }

  return `${normalizedValue.slice(0, maxLength - 3)}...`;
}

function getColumnWidths(columns: TableExportColumn[]): number[] {
  const columnCount = Math.max(columns.length, 1);
  const contentWidth = PDF_PAGE_WIDTH - (PDF_PAGE_MARGIN * 2);
  const columnWidth = contentWidth / columnCount;

  return columns.map((): number => columnWidth);
}

function getCellMaxLength(width: number): number {
  return Math.max(Math.floor((width - (PDF_CELL_PADDING * 2)) / (PDF_TABLE_FONT_SIZE * 0.58)), 3);
}

function pushRectangle(
  commands: string[],
  xPosition: number,
  yPosition: number,
  width: number,
  height: number,
  fillColor: string,
): void {
  commands.push(`q ${fillColor} rg ${xPosition} ${yPosition - height} ${width} ${height} re f Q`);
}

function pushCellBorder(
  commands: string[],
  xPosition: number,
  yPosition: number,
  width: number,
  height: number,
): void {
  commands.push(`q 0.82 0.85 0.90 RG ${xPosition} ${yPosition - height} ${width} ${height} re S Q`);
}

function pushText(
  commands: string[],
  value: string,
  xPosition: number,
  yPosition: number,
  fontSize: number,
): void {
  commands.push('BT');
  commands.push(`/F1 ${fontSize} Tf`);
  commands.push(`1 0 0 1 ${xPosition} ${yPosition} Tm`);
  commands.push(`(${getPdfTextLiteral(value)}) Tj`);
  commands.push('ET');
}

function pushTableHeader(
  commands: string[],
  columns: TableExportColumn[],
  columnWidths: number[],
  yPosition: number,
): void {
  let xPosition = PDF_PAGE_MARGIN;

  columns.forEach((column: TableExportColumn, index: number): void => {
    const width = columnWidths[index] ?? 0;
    const maxLength = getCellMaxLength(width);

    pushRectangle(commands, xPosition, yPosition, width, PDF_HEADER_HEIGHT, '0.90 0.92 0.95');
    pushCellBorder(commands, xPosition, yPosition, width, PDF_HEADER_HEIGHT);
    pushText(
      commands,
      getTruncatedText(column.label, maxLength),
      xPosition + PDF_CELL_PADDING,
      yPosition - 13,
      PDF_TABLE_FONT_SIZE,
    );
    xPosition += width;
  });
}

function pushTableRow(
  commands: string[],
  columns: TableExportColumn[],
  columnWidths: number[],
  record: TableExportRecord,
  rowIndex: number,
  yPosition: number,
): void {
  const fillColor = rowIndex % 2 === 0 ? '1 1 1' : '0.96 0.97 0.98';
  let xPosition = PDF_PAGE_MARGIN;

  columns.forEach((column: TableExportColumn, index: number): void => {
    const width = columnWidths[index] ?? 0;
    const maxLength = getCellMaxLength(width);

    pushRectangle(commands, xPosition, yPosition, width, PDF_ROW_HEIGHT, fillColor);
    pushCellBorder(commands, xPosition, yPosition, width, PDF_ROW_HEIGHT);
    pushText(
      commands,
      getTruncatedText(record[column.name] ?? '', maxLength),
      xPosition + PDF_CELL_PADDING,
      yPosition - 12,
      PDF_TABLE_FONT_SIZE,
    );
    xPosition += width;
  });
}

function createPdfPage(
  columns: TableExportColumn[],
  columnWidths: number[],
  title: string,
  limit: number,
  pageNumber: number,
): { commands: string[]; yPosition: number } {
  const commands: string[] = [];
  let yPosition = PDF_PAGE_HEIGHT - PDF_PAGE_MARGIN;

  pushText(commands, title, PDF_PAGE_MARGIN, yPosition, PDF_TITLE_FONT_SIZE);
  yPosition -= 16;
  pushText(
    commands,
    `Exportado em ${new Date().toLocaleString('pt-BR')} - limite de ${limit} registros - pagina ${pageNumber}`,
    PDF_PAGE_MARGIN,
    yPosition,
    PDF_META_FONT_SIZE,
  );
  yPosition = PDF_PAGE_HEIGHT - PDF_PAGE_MARGIN - PDF_TITLE_BLOCK_HEIGHT;
  pushTableHeader(commands, columns, columnWidths, yPosition);

  return {
    commands,
    yPosition: yPosition - PDF_HEADER_HEIGHT,
  };
}

function buildPdfPageContents(
  columns: TableExportColumn[],
  records: TableExportRecord[],
  title: string,
  limit: number,
): string[] {
  const columnWidths = getColumnWidths(columns);
  const pages: string[] = [];
  let pageNumber = 1;
  let page = createPdfPage(columns, columnWidths, title, limit, pageNumber);

  if (records.length === 0) {
    pushText(page.commands, 'Nenhum registro encontrado.', PDF_PAGE_MARGIN, page.yPosition - 12, PDF_TABLE_FONT_SIZE);
    pages.push(page.commands.join('\n'));

    return pages;
  }

  records.forEach((record: TableExportRecord, index: number): void => {
    if (page.yPosition - PDF_ROW_HEIGHT < PDF_PAGE_MARGIN) {
      pages.push(page.commands.join('\n'));
      pageNumber++;
      page = createPdfPage(columns, columnWidths, title, limit, pageNumber);
    }

    pushTableRow(page.commands, columns, columnWidths, record, index, page.yPosition);
    page.yPosition -= PDF_ROW_HEIGHT;
  });

  pages.push(page.commands.join('\n'));

  return pages;
}

function buildPdfDocument(
  columns: TableExportColumn[],
  records: TableExportRecord[],
  title: string,
  limit: number,
): Blob {
  const pageContents = buildPdfPageContents(columns, records, title, limit);
  const objects: string[] = [
    '<< /Type /Catalog /Pages 2 0 R >>',
    '',
    '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
  ];
  const pageReferences: string[] = [];

  pageContents.forEach((content: string): void => {
    const contentObjectNumber = objects.length + 1;

    objects.push(`<< /Length ${content.length} >>\nstream\n${content}\nendstream`);

    const pageObjectNumber = objects.length + 1;

    objects.push(
      `<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ${PDF_PAGE_WIDTH} ${PDF_PAGE_HEIGHT}] `
      + `/Resources << /Font << /F1 3 0 R >> >> /Contents ${contentObjectNumber} 0 R >>`,
    );
    pageReferences.push(`${pageObjectNumber} 0 R`);
  });

  objects[1] = `<< /Type /Pages /Kids [${pageReferences.join(' ')}] /Count ${pageReferences.length} >>`;

  let pdfContent = '%PDF-1.4\n';
  const offsets = [0];

  objects.forEach((object: string, index: number): void => {
    offsets.push(pdfContent.length);
    pdfContent += `${index + 1} 0 obj\n${object}\nendobj\n`;
  });

  const xrefOffset = pdfContent.length;

  pdfContent += `xref\n0 ${objects.length + 1}\n`;
  pdfContent += '0000000000 65535 f \n';

  offsets.slice(1).forEach((offset: number): void => {
    pdfContent += `${String(offset).padStart(10, '0')} 00000 n \n`;
  });

  pdfContent += `trailer\n<< /Size ${objects.length + 1} /Root 1 0 R >>\n`;
  pdfContent += `startxref\n${xrefOffset}\n%%EOF`;

  return new Blob([pdfContent], { type: 'application/pdf' });
}

export function buildTableExportBlob(
  format: TableExportFormat,
  columns: TableExportColumn[],
  records: TableExportRecord[],
  title: string,
  limit: number,
): Blob {
  if (format === 'pdf') {
    return buildPdfDocument(columns, records, title, limit);
  }

  return new Blob([buildXlsContent(columns, records)], { type: 'application/vnd.ms-excel;charset=utf-8' });
}
