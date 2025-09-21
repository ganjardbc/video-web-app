/**
 * Calculate table row number based on pagination state
 * @param index - Zero-based index of the current row
 * @param page - Current page number (1-based)
 * @param rowsPerPage - Number of rows per page
 * @returns The display number for the table row
 */
export const getNoTable = (index: number, page: number, rowsPerPage: number): number => {
  return (page - 1) * rowsPerPage + index + 1;
};
